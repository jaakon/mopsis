<?php
namespace Mopsis\Extensions\Aura\Filter\Rule\Validate;

class Optional
{
    public function __invoke($subject, $field)
    {
        return true;
    }
}
