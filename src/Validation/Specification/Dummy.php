<?php namespace Mopsis\Validation\Specification;

class Dummy implements iRequestSpecification
{
	private $_fieldname;
	private $_value;

	public function __construct($fieldname, $value = true)
	{
		$this->_fieldname	= $fieldname;
		$this->_value		= $value;
	}

	public function getValidatedField()
	{
		return $this->_fieldname;
	}

	public function isSatisfiedBy(\Mopsis\Validation\ValidationCoordinator $coordinator)
	{
		return $this->_value;
	}
}
