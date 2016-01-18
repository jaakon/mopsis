<?php namespace Mopsis\Extensions\Aura\Filter\Rule\Sanitize;

class ArrayValue
{
	public function __invoke($subject, $field)
	{
		$value = $subject->$field;

		if (!is_string($value)) {
			return false;
		}

		$subject->$field = array_values(array_filter(explode(PHP_EOL, $value), 'strlen'));

		return true;
	}
}
