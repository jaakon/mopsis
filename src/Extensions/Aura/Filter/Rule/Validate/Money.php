<?php namespace Mopsis\Extensions\Aura\Filter\Rule\Validate;

class Money
{
	public function __invoke($subject, $field)
	{
		$value = $subject->$field;

		if (!is_scalar($value)) {
			return false;
		}

		return (bool)preg_match('/^-?[0-9]{1,3}([0-9]*|(\.[0-9]{3})*)(,[0-9]{2})?$/', $value);
	}
}
