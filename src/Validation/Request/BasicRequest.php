<?php namespace Mopsis\Validation\Request;

abstract class BasicRequest
{
	protected $_data = null;

	public function __construct()
	{
		$this->_data = new \stdClass();
	}

	public function toArray()
	{
		return (array) $this->_data;
	}

	public function __isset($var)
	{
		return isset($this->_data->$var);
	}

	public function __get($var)
	{
		return property_exists($this->_data, $var) ? $this->_data->$var : false;
	}

	/** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
	public function __set($var, $value)
	{
		throw new \Exception('write access for Request->' . $var . ' denied!');
	}

	protected function _anatomizeKeys($data)
	{
		$newKeys = [];

		foreach ($data as $key => $value) {
			if (!preg_match('/^([^\.]+)\.(.*)$/', $key, $m)) {
				continue;
			}

			unset($data->{$key});

			if (!isset($data->{$m[1]})) {
				$data->{$m[1]} = new \stdClass();
				$newKeys[]     = $m[1];
			}

			$data->{$m[1]}->{$m[2]} = is_string($value) ? trim(strip_tags($value)) : $value;
		}

		if (count($newKeys)) {
			foreach ($newKeys as $key) {
				$data->$key = $this->_anatomizeKeys($data->$key);
			}
		}

		return $data;
	}

	protected function _getCombinedRequest()
	{
		$requestOrder = ini_get('request_order') ?: 'GP';
		$strPosGet    = strpos($requestOrder, 'G');
		$strPosPost   = strpos($requestOrder, 'P');

		if ($strPosPost === false) {
			return $this->_getGetParameters();
		}

		if ($strPosGet === false) {
			return $this->_getPostParameters();
		}

		if ($strPosGet > $strPosPost) {
			return array_merge($this->_getPostParameters(), $this->_getGetParameters());
		}

		return array_merge($this->_getGetParameters(), $this->_getPostParameters());
	}

	private function _getGetParameters()
	{
		return $_GET;
	}

	private function _getPostParameters()
	{
		$request = [];

		foreach (explode('&', file_get_contents('php://input')) as $entry) {
			list($key,)    = array_map('urldecode', explode('=', $entry));
			$key           = preg_replace('/\[(.*)\]$/', '', $key);
			$request[$key] = $_REQUEST[str_replace(['.', ' '], '_', $key)];
		}

		return $request;
	}
}
