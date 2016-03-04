<?php
namespace Mopsis\Extensions\Aura\Filter\Rule\Validate\DateTime;

use Carbon\Carbon;

class AbstractDateTime
{
    protected function castValueToDateTime($value)
    {
        if ($value instanceof Carbon) {
            return $value;
        }

        if ($value instanceof \DateTime) {
            return new Carbon($value);
        }

        if (is_scalar($value)) {
            return Carbon::parse($value);
        }

        throw new \Exception('invalid value');
    }
}
