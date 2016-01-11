<?php namespace Mopsis\Extensions\FluentDao;

/**
 * @property  properties
 */
abstract class Container
{
	protected $data  = null;
	protected $cache = [];

	//=== PUBLIC METHODS ===========================================================

	public function __construct($criteria = null)
	{
		$this->data = array_fill_keys($this->properties, null);

		if (is_array($criteria)) {
			foreach ($criteria as $key => $value) {
				$this->$key = $value;
			}
		}
	}

	public function __isset($key)
	{
		if (isset($this->data[$key])) {
			return true;
		}

		if (method_exists($this, 'get' . ucfirst($key) . 'Attribute')) {
			$value = $this->{'get' . ucfirst($key) . 'Attribute'}();

			return isset($value);
		}

		return false;
	}

	public function __get($key)
	{
		if (method_exists($this, 'get' . ucfirst($key) . 'Attribute')) {
			if ($this->cache[$key] === null) {
				$this->cache[$key] = $this->{'get' . ucfirst($key) . 'Attribute'}();
			}

			return $this->cache[$key];
		}

		return $this->_get($key);
	}

	public function __set($key, $value)
	{
		unset($this->cache[$key]);

		if (method_exists($this, 'set' . ucfirst($key) . 'Attribute')) {
			return $this->{'set' . ucfirst($key) . 'Attribute'}($value);
		}

		return $this->_set($key, $value);
	}

	protected function _get($key)
	{
		$key = ltrim($key, '_');

		if (array_key_exists($key, $this->data)) {
			return $this->data[$key];
		}

		throw new \Exception('property [' . $key . '] is undefined');
	}

	//=== PROTECTED METHODS ========================================================

	protected function _set($key, $value)
	{
		if (array_key_exists($key, $this->data)) {
			$this->data[$key] = $value;

			return true;
		}

		throw new \Exception('property [' . $key . '] is undefined');
	}

	public function set($key, $value)
	{
		$this->$key = $value;

		return $this;
	}
}
