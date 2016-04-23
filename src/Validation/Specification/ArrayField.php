<?php namespace Mopsis\Validation\Specification;

class ArrayField implements iRequestSpecification
{
	private $_fieldname;
	private $_valueSpecification;

	public function __construct($fieldname, iArraySpecification $specification)
	{
		$this->_fieldname			= $fieldname;
		$this->_valueSpecification	= $specification;
	}

	public function getValidatedField()
	{
		return $this->_fieldname;
	}

	public function isSatisfiedBy(\Mopsis\Validation\ValidationCoordinator $coordinator)
	{
		return is_array($coordinator->get($this->_fieldname)) && $this->_valueSpecification->isSatisfiedBy($coordinator->get($this->_fieldname));
	}
}
