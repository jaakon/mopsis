<?php namespace Mopsis\Validation;

class ValidationCoordinator
{
	private $_raw;
	private $_clean;
	private $_errors = [];
	private $_fields = [];

	public function __construct(Request\RawRequest $raw, Request\CleanRequest $clean)
	{
		$this->_raw		= $raw;
		$this->_clean	= $clean;
	}

	public function get($name)
	{
		return $this->_raw->getForValidation($name);
	}

	public function setClean($name = null)
	{
		if ($name === null)
			return false;

		$this->_clean = $this->_clean->set($name, $this->_raw->getForValidation($name), false);
	}

	public function addError($error)
	{
		$this->_errors[] = $error;
	}

	public function getErrors()
	{
		return $this->_errors;
	}

	public function addInvalidField($field)
	{
		$this->_fields[] = $field;
	}

	public function getInvalidFields()
	{
		return $this->_fields;
	}

	public function getCleanRequest()
	{
		return $this->_clean;
	}
}
