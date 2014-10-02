<?php namespace Mopsis\Validation\Specification;

class RegExp implements iValueSpecification
{
	private $_regexp;
	private $_strict;

	public function __construct($regexp, $strict = true)
	{
		$this->_regexp = $regexp;
		$this->_strict = $strict;
	}

	public function isSatisfiedBy($value)
	{
		return preg_match($this->_regexp, $value) || (!$this->_strict && !strlen($value));
	}
}
