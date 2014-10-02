<?php namespace Mopsis\Validation\Specification;

class CountCoreObjects implements iValueSpecification
{
	private $_object;
	private $_attribute;

	public function __construct($object, $attribute)
	{
		$this->_object		= $object;
		$this->_attribute	= $attribute;
	}

	public function isSatisfiedBy($value)
	{
		if (is_string($this->_object) && class_exists($this->_object))
			return call_user_func_array($this->_object.'::count', [$this->_attribute.'=?', $value]) === 0;

		return $this->_object->{$this->_attribute} == $value || call_user_func_array(get_class($this->_object).'::count', [$this->_attribute.'=?', $value]) === 0;
	}
}
