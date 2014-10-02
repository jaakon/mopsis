<?php namespace Mopsis\Validation\Specification;

class RequiredValue implements iValueSpecification
{
	public function isSatisfiedBy($value)
	{
		return (is_string($value) && !!strlen($value)) || (is_array($value) && !!count($value));
	}
}
