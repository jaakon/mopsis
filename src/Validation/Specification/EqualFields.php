<?php namespace Mopsis\Validation\Specification;

class EqualFields implements iRequestSpecification
{
	private $_fieldOne;
	private $_fieldTwo;

	public function __construct($fieldOne, $fieldTwo)
	{
		$this->_fieldOne = $fieldOne;
		$this->_fieldTwo = $fieldTwo;
	}

	public function getValidatedField()
	{
		return $this->_fieldOne;
	}

	public function isSatisfiedBy(\Mopsis\Validation\ValidationCoordinator $coordinator)
	{
		return $coordinator->get($this->_fieldOne) === $coordinator->get($this->_fieldTwo);
	}
}
