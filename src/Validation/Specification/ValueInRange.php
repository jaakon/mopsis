<?php namespace Mopsis\Validation\Specification;

class ValueInRange implements iValueSpecification
{
	private $_minLength;
	private $_maxLength;

	public function __construct($minValue, $maxValue)
	{
		$this->_minValue = $minValue;
		$this->_maxValue = $maxValue;
	}

	public function isSatisfiedBy($value)
	{
		return
			($this->_minValue === false || $value >= $this->_minValue)
		 && ($this->_maxValue === false || $value <= $this->_maxValue);
	}
}
