<?php
namespace Mopsis\Extensions\Aura\Filter\Rule\Validate\DateTime;

class After extends AbstractDateTime
{
    public function __invoke($subject, $field, $datetime)
    {
        if (is_scalar($datetime)) {
            $datetime = $subject->$datetime;
        }

        return isset($datetime) && $this->castValueToDateTime($subject->$field)->gt($this->castValueToDateTime($datetime));
    }
}
