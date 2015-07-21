<?php namespace Mopsis\Types;

class Token
{
	protected $instance;
	protected $session;

	public function __construct(\Mopsis\Eloquent\Model $instance, $session = null)
	{
		$this->instance = $instance;
		$this->session = $session;
	}

	public static function extract($string)
	{
		$string = (string)$string;

		if (!preg_match('/^(\w+):(\d+):[a-f0-9]+$/i', $string, $m)) {
			return false;
		}

		try {
			$namespaced = \Mopsis\Core\App::make('namespacedModels');
			$class = sprintf($namespaced, str_plural($m[1]), $m[1]);
			$instance = $class::findOrFail($m[2]);
		} catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
			return false;
		}

		if ($string !== $instance->token->generate() && $string !== $instance->hash->generate()) {
			return false;
		}

		return $instance;
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
