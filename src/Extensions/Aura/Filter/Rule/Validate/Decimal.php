<?php
namespace Mopsis\Extensions\Aura\Filter\Rule\Validate;

class Decimal
{
    public function __invoke($subject, $field)
    {
        $value = $subject->$field;

        if (!is_scalar($value)) {
            return false;
        }

        return (bool) preg_match('/^-?\d{1,3}(\d*|(\.\d{3})*)(,\d{1,})?$/', $value);
    }
}
