<?php namespace Mopsis\Validation\Specification;

class DependentFields implements iRequestSpecification
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
		return $this->_isEmpty($coordinator->get($this->_fieldOne)) || !$this->_isEmpty($coordinator->get($this->_fieldTwo));
	}

	private function _isEmpty($value)
	{
		return (is_string($value) && !strlen($value)) || (is_array($value) && !count($value));
	}
}
