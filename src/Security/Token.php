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

		if (!preg_match('/^(\w+?)(?:Model)?:(\d+):[a-f0-9]+$/i', $string, $m)) {
			return false;
		}

		try {
			$class = App::build('Domain', str_plural($m[1]) . '\\' . $m[1] . '\\Model');
		} catch (\DomainException $e) {
			$class = App::build('Model', str_plural($m[1]) . '\\' . $m[1]);
		}

		try {
			$instance = $class::findOrFail($m[2]);
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
		return $this->instance . ':' . sha1(get_class($this->instance) . $this->instance->id . config('app.key') . $this->session);
	}
}
