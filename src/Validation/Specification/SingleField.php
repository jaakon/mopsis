<?php namespace Mopsis\Validation\Specification;

class SingleField implements iRequestSpecification
{
	private $_fieldname;
	private $_valueSpecification;

	public function __construct($fieldname, iValueSpecification $specification)
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
		return $this->_valueSpecification->isSatisfiedBy($coordinator->get($this->_fieldname));
	}
}
