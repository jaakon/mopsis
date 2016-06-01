<?php namespace Mopsis\Validation\Request;

class RawRequest extends BasicRequest
{
	public function __construct()
	{
		$this->_data = $this->_anatomizeKeys((object) $this->_getCombinedRequest());
	}

	public function __set($var, $value)
	{
		$this->_data->$var = $value;
	}

	public function getForValidation($var)
	{
		if (preg_match('/^(\w+)\[(\w+)\]$/', $var, $m)) {
			return $this->_data->{$m[1]}[$m[2]];
		}

		if (strpos($var, '.') === false) {
			return $this->_data->$var;
		}

		$result = clone $this->_data;

		foreach (explode('.', $var) as $key) {
			$result = $result->$key;
		}

		return $result;
	}

	public function toArray()
	{
		$results = [];

		foreach ((array) $this->_data as $key => $value) {
			if (!is_object($value)) {
				$results[$key] = $value;
				continue;
			}

			foreach ((array) $value as $subkey => $subvalue) {
				$results[$key . '.' . $subkey] = $subvalue;
			}
		}

		return $results;
	}
}
