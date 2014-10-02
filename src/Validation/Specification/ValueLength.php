<?php namespace Mopsis\Validation\Specification;

class ValueLength implements iValueSpecification
{
	private $_minLength;
	private $_maxLength;

	public function __construct($minLength, $maxLength)
	{
		$this->_minLength = $minLength;
		$this->_maxLength = $maxLength;
	}

	public function isSatisfiedBy($value)
	{
		return
			($this->_minLength === false || mb_strlen($value) >= $this->_minLength)
		 && ($this->_maxLength === false || mb_strlen($value) <= $this->_maxLength);
	}
}
