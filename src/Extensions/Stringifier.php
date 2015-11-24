<?php namespace Mopsis\Extensions;

use DateTime;
use Exception;
use stdClass;
use Mopsis\Contracts\Model;

class Stringifier
{
	public function __construct($object)
	{
		$this->object = $object;
	}

	public function __get($key)
	{
		if ($this->objectHasAsStringMutator($key)) {
			return $this->objectGetAsStringMutator($key);
		}

		$value = $this->object->$key;

		if (is_string($value)) {
			return $value;
		}

		return $this->castValueToString($value);
	}

	public function __isset($key)
	{
		return true; // for Twig
	}

	public function toArray()
	{
		return $this->castArrayValuesToString(object_to_array($this->object));
	}

	protected function castArrayValuesToString(array $array)
	{
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$array[$key] = $this->castArrayValuesToString($value);
				continue;
			}

			$array[$key] = $this->$key ?: $this->castValueToString($value);
		}

		return $array;
	}

	protected function castFloatToString($float)
	{
		$locale = localeconv();

		return preg_replace('/(?:(,\d*[1-9])|,)0+$/', '$1', number_format( // trim tailing zeros
			$float,
			$locale['frac_digits'],
			$locale['decimal_point'],
			$locale['thousands_sep']
		));
	}

	protected function castObjectToString($object)
	{
		if ($object instanceof DateTime) {
			return $object->format(config('stringify.datetime'));
		}

		if (method_exists($object, '__toString')) {
			return (string) $object;
		}

		if ($object instanceof stdClass) {
//			return json_encode(object_to_array($object));
		}

		throw new Exception('cannot cast instance of ' . get_class($object) . ' to string');
	}

	protected function castValueToString($value)
	{
		switch (gettype($value)) {
			case 'NULL':
				return '';
			case 'string':
				return $value;
			case 'boolean':
				return $value ? 1 : 0;
			case 'integer':
				return (string) $value;
			case 'double':
			case 'float':
				return $this->castFloatToString($value);
			case 'object':
				return $this->castObjectToString($value);
			case 'array':
				return json_encode($this->castArrayValuesToString($value));
		}

		if (is_callable($value)) {
			return $this->castValueToString($value());
		}

		throw new \Exception('cannot cast value of type "' . gettype($value) . '" to string');
	}

	protected function objectGetAsStringMutator($key)
	{
		return $this->object->{'get' . studly_case($key) . 'AsStringAttribute'}();
	}

	protected function objectHasAsStringMutator($key)
	{
		return method_exists($this->object, 'get' . studly_case($key) . 'AsStringAttribute');
	}
}
