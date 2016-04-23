<?php namespace Mopsis\Validation\Specification;

class EqualsValue implements iValueSpecification
{
	private $_value;

	public function __construct($value)
	{
		$this->_value = $value;
	}

	public function isSatisfiedBy($value)
	{
		return $value == $this->_value;
	}
}
