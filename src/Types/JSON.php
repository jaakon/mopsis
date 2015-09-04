<?php namespace Mopsis\Types;

class JSON
{
	private $_data;

	public function __construct($data)
	{
		switch (gettype($data)) {
			case 'array':
				$this->_data = $data;
				break;
			case 'object':
				$this->_data = method_exists($data, 'toArray') ? $data->toArray() : get_object_vars($data);
				break;
			case 'string':
				$this->_data = json_decode($data, true)?: [];
				break;
			default:
				$this->_data = [];
				break;
		}
	}

	public function __isset($key)
	{
		return array_key_exists($key, $this->_data);
	}

	public function __get($key)
	{
		return $this->_data[$key] ?: null;
	}

	public function __set($key, $value)
	{
		$this->_data[$key] = $value;
	}

	public function __toString()
	{
		return count($this->_data) ? json_encode($this->_data) : '';
	}

	public function length()
	{
		return count($this->_data);
	}

	public function toArray()
	{
		return $this->_data;
	}
}
