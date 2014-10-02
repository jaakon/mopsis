<?php namespace Mopsis\Validation\Specification;

class LoadCoreObject implements iValueSpecification
{
	private $_object;
	private $_attribute;

	public function __construct($model)
	{
		$this->_model = $model;
	}

	public function isSatisfiedBy($value)
	{
		return !!(call_user_func([$this->_model, 'load'], $value));
	}
}
