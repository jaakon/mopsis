<?php namespace Mopsis\Filter\Rule\Sanitize;

class Float
{
	public function __invoke($subject, $field)
	{
		$value = $subject->$field;

		if (!is_string($value)) {
			return false;
		}

		if (ctype_digit($value)) {
			$subject->$field = (float)$value;

			return true;
		}

		$locales = ['de_DE', 'en_US', 'fr_FR']; //\ResourceBundle::getLocales('');

		foreach ($locales as $locale) {
			$fmt = new \NumberFormatter($locale, \NumberFormatter::DECIMAL);
			$float = $fmt->parse($value);

			if ($float !== false) {
				$subject->$field = (float)$float;

				return true;
			}
		}

		return false;
	}
}
