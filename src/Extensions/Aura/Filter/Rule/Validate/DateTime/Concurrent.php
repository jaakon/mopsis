<?php
namespace Mopsis\Extensions\Aura\Filter\Rule\Validate\DateTime;

class Concurrent extends AbstractDateTime
{
    public function __invoke($subject, $field, $datetime, $format = 'Y-m-d H:i:s')
    {
        if (is_scalar($datetime)) {
            $datetime = $subject->$datetime;
        }

        return isset($datetime) && $this->castValueToDateTime($subject->$field)->format($format) === $this->castValueToDateTime($datetime)->format($format);
    }
}
