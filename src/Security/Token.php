<?php namespace Mopsis\Security;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mopsis\Contracts\Model;
use Mopsis\Core\App;

class Token
{
	protected $instance;
	protected $session;

	public static function extract($string)
	{
		$string = (string)$string;

		if (!preg_match('/^(\w+):(\d+):[a-f0-9]+$/i', $string, $m)) {
			return false;
		}

		try {
			$class    = App::create('Domain', str_plural($m[1]) . '\\' . $m[1] . '\\Model');
			$instance = $class::findOrFail($m[2]);
		} catch (\DomainException $e) {
			$instance = App::create('Model', str_plural($m[1]) . '\\' . $m[1], ['id' => $m[2]]);
		} catch (ModelNotFoundException $e) {
			return false;
		}

		if ($string !== $instance->token->generate() && $string !== $instance->hash->generate()) {
			return false;
		}

		return $instance;
	}

	public function __construct(Model $instance, $session = null)
	{
		$this->instance = $instance;
		$this->session  = $session;
	}

	public function __toString()
	{
		return $this->generate();
	}

	public function generate()
	{
		return $this->instance . ':' . sha1(get_class($this->instance) . $this->instance->id . CORE_SALT . $this->session);
	}
}