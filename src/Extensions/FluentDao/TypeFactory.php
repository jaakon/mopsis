<?php
namespace Mopsis\Extensions\FluentDao;

use Mopsis\Contracts\Model as ModelContract;
use Mopsis\Core\App;

class TypeFactory
{
    public static function cast($value, $type)
    {
        if ($value === null) {
            return self::castNullToType($type);
        }

        return self::castValueToType($value, $type);
    }

    public static function create($type, $value = null)
    {
        return $value instanceof $type ? $value : new $type($value);
    }

    protected static function castNullToType($type)
    {
        switch ($type) {
            case 'json':
                return self::create('\Mopsis\Extensions\Json');
        }

        return;
    }

    protected static function castValueToType($value, $type)
    {
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
                return self::create('\Mopsis\Extensions\Carbon\Carbon', $value ?: 'now');
            case 'enum':
                return (string) $value;
            case 'json':
                return self::create('\Mopsis\Extensions\Json', $value);
            case 'model':
                if ($value instanceof ModelContract) {
                    return $value;
                }

                if (preg_match('/^([a-z]+):(\d+)$/i', $value, $m)) {
                    try {
                        $model = App::build('Model', $m[1]);
                    } catch (\DomainException $e) {
                        $model = App::build('Model', $m[1] . 's');
                    }

                    try {
                        return ModelFactory::load($model, $m[2]);
                    } catch (\LengthException $e) {
                        throw new \Exception('reference "' . $value . '" is invalid');
                    }
                }
        }

        return $value;
    }
}
