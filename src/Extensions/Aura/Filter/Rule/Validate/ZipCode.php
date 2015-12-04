<?php namespace Mopsis\Extensions\Aura\Filter\Rule\Validate;

class ZipCode
{
	public function __invoke($subject, $field)
	{
		$value = $subject->$field;

		if (!is_scalar($value)) {
			return false;
		}

		return (bool) preg_match('/^[0-9]{5}$/', $value);
	}
}
