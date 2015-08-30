<?php namespace Mopsis\Types;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mopsis\Extensions\Eloquent\Model;
use Mopsis\Core\App;

class Token
{
	protected $instance;
	protected $session;

	public static function extract($string)
	{
		$string = (string)$string;

		if (!preg_match('/^((\w+):(\d+)):[a-f0-9]+(?:~(\w+))?$/i', $string, $m)) {
			return false;
		}

		try {
			$replacements = [
				'{{MODULE}}'   => str_plural($m[1]),
				'{{DOMAIN}}'   => $m[1]
			];

			$class      = str_replace(array_keys($replacements), array_values($replacements), App::make('namespacedModels'));
			$instance   = $class::findOrFail($m[2]);
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
