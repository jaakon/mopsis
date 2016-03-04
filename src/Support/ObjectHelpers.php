<?php
namespace Mopsis\Support;

use stdClass;

class ObjectHelpers
{
    public static function merge(stdClass $baseObject, stdClass...$objects)
    {
        $result = clone $baseObject;

        foreach ($objects as $object) {
            foreach (get_object_vars($object) as $key => $value) {
                if (!isset($result->$key) || gettype($result->$key) !== gettype($value)) {
                    $result->$key = $value;
                    continue;
                }

                switch (gettype($value)) {
                    case 'array':
                        $result->$key = array_merge_recursive($result->$key, $value);
                        break;
                    case 'object':
                        $result->$key = static::merge($result->$key, $value);
                        break;
                    default:
                        $result->$key = $value;
                        break;
                }
            }
        }

        return $result;
    }

    public static function toArray($object)
    {
        if (null === $object) {
            return [];
        }

        if (is_array($object)) {
            return $object;
        }

        if (!is_object($object)) {
            throw new \Exception('cannot cast ' . gettype($object) . ' to array');
        }

        if ($object instanceof \ArrayObject) {
            return $object->getArrayCopy();
        }

        if ($object instanceof \Traversable) {
            return iterator_to_array($object, true);
        }

        if (method_exists($object, 'toArray')) {
            return $object->toArray();
        }

        return get_object_vars($object);
    }
}
