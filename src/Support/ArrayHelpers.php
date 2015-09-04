<?php namespace Mopsis\Support;

class ArrayHelpers
{
	public static function concat(array $array, ...$values)
	{
		foreach ($values as $value) {
			switch (gettype($value)) {
				case 'array':
					$array = array_merge_recursive($array, $value);
					break;
				case 'object':
					$array = array_merge_recursive($array, ObjectHelpers::toArray($value));
				// no break
				default:
					$array[] = $value;
			}
		}

		return $array;
	}

	public static function diffValues(array $array1, array $array2)
	{
		$diff = [];

		foreach (array_unique(array_merge(array_keys($array1), array_keys($array2))) as $key) {
			if (is_array($array1[$key]) && is_array($array2[$key])) {
				$diff[$key] = static::diffValues(
					$array1[$key],
					$array2[$key]
				);
			} elseif (is_object($array1[$key]) && is_object($array2[$key])) {
				$diff[$key] = static::diffValues(
					ObjectHelpers::toArray($array1[$key]),
					ObjectHelpers::toArray($array2[$key])
				);
			} elseif ((string) $array1[$key] !== (string) $array2[$key]) {
				$diff[$key] = [
					(string) $array1[$key],
					(string) $array2[$key]
				];
			}
		}

		return array_filter($diff);
	}

	public static function value($array, $key)
	{
		return $array[$key];
	}

	public static function wrap($array)
	{
		return is_array($array) ? $array : [$array];
	}
}