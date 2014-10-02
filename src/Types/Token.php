<?php namespace Mopsis\Types;

class Token
{
	private $model;
	private $session;

	public static function extract($string, $salt = null)
	{
		$string = (string) $string;

		if (!preg_match('/^((\w+):(\d+)):[a-f0-9]+$/i', $string, $m)) {
			return false;
		}

		try {
			$class    = '\\Models\\'.$m[2];
			$instance = $class::findOrFail($m[3]);
		} catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
			return false;
		}

		return $string === $instance->token->generate($salt) || $string === $instance->hash->generate($salt) ? $instance : false;
	}

	public function __construct(\Mopsis\Eloquent\Model $model, $session = null)
	{
		$this->model   = $model;
		$this->session = $session;
	}

	public function __toString()
	{
		return $this->generate();
	}

	public function generate($salt = null)
	{
		return $this->model.':'.sha1(get_class($this->model).$this->model->id.CORE_SALT.$salt);
	}
}
