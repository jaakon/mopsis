<?php
namespace Mopsis\Extensions\Aura\Filter\Rule\Sanitize;

class FloatValue
{
    public function __invoke($subject, $field)
    {
        $value = $subject->$field;

        if (!is_string($value)) {
            return false;
        }

// duration: hh:mm:ss
        if (preg_match('/^(-?\d+):(\d{2})(?::(\d{2}))?$/', $value, $m)) {
            $value = abs($m[1]) + $m[2] / 60 + $m[3] / 3600;

            if ($m[1][0] === '-') {
                $value *= -1;
            }
        }

        if (ctype_digit($value)) {
            $subject->$field = (float) $value;

            return true;
        }

        $locales = [
            'de_DE',
            'en_US',
            'fr_FR'
        ];

//\ResourceBundle::getLocales('');

        foreach ($locales as $locale) {
            $fmt   = new \NumberFormatter($locale, \NumberFormatter::DECIMAL);
            $float = $fmt->parse($value);

            if ($float !== false) {
                $subject->$field = (float) $float;

                return true;
            }
        }

        return false;
    }
}
