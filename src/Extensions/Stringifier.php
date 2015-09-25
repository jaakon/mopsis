<?php namespace Mopsis\Extensions;

class Stringifier
{
	protected $object;

	public function __construct($object)
	{
		$this->object = $object;
	}

	public function __get($key)
	{
		return $this->stringify($this->getValue($key));
	}

	public function __isset($key)
	{
		return true; // for Twig
	}

	public function toArray()
	{
		$data = method_exists($this->object, 'toArray') ? $this->object->toArray() : get_object_vars($this->object);

		return $this->stringifyArray($data);
	}

	protected function getValue($key)
	{
		if ($this->objectHasAsStringMutator($key)) {
			return $this->objectGetAsStringMutator($key);
		}

		return $this->object->$key;
	}

	protected function objectGetAsStringMutator($key)
	{
		return $this->object->{'get' . studly_case($key) . 'AsStringAttribute'}();
	}

	protected function objectHasAsStringMutator($key)
	{
		return method_exists($this->object, 'get' . studly_case($key) . 'AsStringAttribute');
	}

	protected function objectToString($object)
	{
		switch (get_class($object)) {
			case 'Carbon\Carbon':
				return $object->format(DATETIME_DE_SHORT);
			default:
				return get_class($object); //(string) $object;
		}
	}

	protected function stringify($value)
	{
		if ($value === null) {
			return '';
		}

		if (is_scalar($value)) {
			return $value === false ? '0' : (string)$value;
		}

		if (is_object($value)) {
			return $this->objectToString($value);
		}

		if (is_array($value)) {
			return json_encode($value);
		}

		if (is_callable($value)) {
			return $this->stringify($value());
		}

		throw new \Exception('cannot stringify value of type "' . gettype($value) . '"');
	}

	protected function stringifyArray(array $data)
	{
		foreach ($data as $key => $value) {
			if (is_array($value)) {
				$data[$key] = $this->stringifyArray($value);
				continue;
			}

			$data[$key] = $this->stringify($this->getValue($key) ?: $value);
		}

		return $data;
	}
}
