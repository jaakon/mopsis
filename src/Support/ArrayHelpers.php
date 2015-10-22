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

	public static function dot($array, $prepend = '')
	{
		$results = [];

		foreach ($array as $key => $value) {
			if (is_array($value) && count($value)) {
				$results = array_merge($results, static::dot($value, $prepend.$key.'.'));
			} else {
				$results[$prepend.$key] = $value;
			}
		}

		return $results;
	}

	public static function trim(array $array, callable $callback = null)
	{
		$array = array_reverse(static::filterWithBreakpoint($array, $callback), true);
		return array_reverse(static::filterWithBreakpoint($array, $callback), true);
	}

	public static function value($array, $key)
	{
		return $array[$key];
	}

	public static function wrap($array)
	{
		return is_array($array) ? $array : [$array];
	}

	protected static function filterWithBreakpoint(array $array, callable $callback = null)
	{
		foreach ($array as $index => $item) {
			if ($callback && $callback($item)) {
				break;
			}
			if (!$callback && $item) {
				break;
			}
			unset($array[$index]);
		}

		return $array;
	}
}
