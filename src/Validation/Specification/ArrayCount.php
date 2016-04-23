<?php namespace Mopsis\Validation\Specification;

class ArrayCount implements iArraySpecification
{
	private $_minLength;
	private $_maxLength;

	public function __construct($minLength, $maxLength)
	{
		$this->_minLength = $minLength;
		$this->_maxLength = $maxLength;
	}

	public function isSatisfiedBy($array)
	{
		return
			($this->_minLength === false || count($array) >= $this->_minLength)
		 && ($this->_maxLength === false || count($array) <= $this->_maxLength);
	}
}
