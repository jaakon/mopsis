<?php
namespace Mopsis\Extensions\Aura\Filter\Rule\Validate;

use Mopsis\Security\Csrf;

class CsrfToken
{
    public function __invoke($subject, $field)
    {
        $fieldValue = $subject->$field;

        if (!is_scalar($fieldValue)) {
            return false;
        }

        list($key, $value) = explode('/', $fieldValue, 2);

        return Csrf::isValidToken($key, $value);
    }
}
