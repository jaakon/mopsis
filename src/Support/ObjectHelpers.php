<?php namespace Mopsis\Support;

class ObjectHelpers
{
	public static function merge(stdClass $object1, stdClass ...$objects)
	{
		$result = clone $object1;

		foreach ($objects as $object) {
			foreach (get_object_vars($object) as $key => $value) {
				if (!isset($result->{$key}) || gettype($result->{$key}) !== gettype($value)) {
					$result->{$key} = $value;
					continue;
				}

				switch (gettype($value)) {
					case 'array':
						$result->{$key} = array_merge_recursive($result->{$key}, $value);
						break;
					case 'object':
						$result->{$key} = static::merge($result->{$key}, $value);
						break;
					default:
						$result->{$key} = $value;
						break;
				}
			}
		}

		return $result;
	}

	public static function toArray($object)
	{
		if (is_null($object)) {
			return [];
		}

		if (is_array($object)) {
			return $object;
		}

		if (!is_object($object)) {
			throw new \Exception('cannot cast given object to array');
		}

		if ($object instanceof \ArrayObject) {
			return $object->getArrayCopy();
		}

		if (method_exists($object, 'toArray')) {
			return $object->toArray();
		}

		return get_object_vars($object);
	}
}
