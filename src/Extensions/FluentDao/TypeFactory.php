<?php
namespace Mopsis\Extensions\FluentDao;

use Mopsis\Contracts\Model as ModelContract;

class TypeFactory
{
    public static function cast($value, $type)
    {
        if ($value === null && $type !== 'json') {
            return $value;
        }

        switch ($type) {
            case 'string':
                return (string) $value;
            case 'integer':
                return (int) $value;
            case 'boolean':
                return (bool) $value;
            case 'float':
            case 'decimal':
                return (float) $value;
            case 'date':
            case 'datetime':
                return $value !== '' ? self::create('\Mopsis\Extensions\Carbon\Carbon', $value ?: 'now'): null;
            case 'enum':
                return (string) $value;
            case 'json':
                return self::create('\Mopsis\Types\JSON', $value);
            case 'model':
                if ($value instanceof ModelContract) {
                    return $value;
                }

                if (preg_match('/^([a-z]+):(\d+)$/i', $value, $m)) {
                    try {
                        return ModelFactory::load($m[1], $m[2]);
                    } catch (\LengthException $e) {
                        throw new \Exception('reference "' . $value . '" is invalid');
                    }
                }
        }

        return $value;
    }

    public static function create($type, $value)
    {
        return $value instanceof $type ? $value : new $type($value);
    }
}
