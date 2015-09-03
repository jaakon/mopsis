<?php namespace Mopsis\Extensions\Aura\Filter\Rule\Validate\Finance;

class Bic
{
	public function __invoke($subject, $field)
	{
		$value = $subject->$field;

		if (!is_scalar($value)) {
			return false;
		}

		return (bool)preg_match('/^([a-zA-Z]){4}([a-zA-Z]){2}([0-9a-zA-Z]){2}([0-9a-zA-Z]{3})?$/', $value);
	}
}
