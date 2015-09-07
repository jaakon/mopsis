<?php namespace Mopsis\Extensions\Aura\Filter\Rule\Validate\DateTime;

class Duration
{
	public function __invoke($subject, $field)
	{
		$value = $subject->$field;

		if (!is_scalar($value)) {
			return false;
		}

		if (is_numeric($value)) {
			$value .= ':00';
		}

		return (bool)preg_match('/^-?\d+:\d{2}(:\d{2})?$/', $value);
	}
}
