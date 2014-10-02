<?php namespace Mopsis\Validation;

class BasicValidator
{
	private $_specification;
	private $_message;
	private $_success;

	public function __construct(Specification\iRequestSpecification $specification, $message, $negate = false)
	{
		$this->_specification = $specification;
		$this->_message       = $message;
		$this->_success       = !$negate;
	}

	public function validate(ValidationCoordinator $coordinator)
	{
		if ($this->_specification->isSatisfiedBy($coordinator)) {
			$coordinator->setClean($this->_specification->getValidatedField());
			return $this->_success;
		} else {
			if ($this->_message) {
				$coordinator->addError($this->_message);
			}

			if (method_exists($this->_specification, 'getValidatedField')) {
				$coordinator->addInvalidField($this->_specification->getValidatedField());
			}

			return !$this->_success;
		}
	}

	public function withMessage($message)
	{
		$this->_message = $message;
		return $this;
	}
}
