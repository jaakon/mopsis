<?php
namespace Mopsis\Extensions\Aura\Filter\Rule\Validate;

class Conditional
{
    public function __invoke($subject, $field, $scalar = true)
    {
        if (!is_scalar($scalar)) {
            return false;
        }

        return !$scalar;
    }
}
