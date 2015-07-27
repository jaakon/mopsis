<?php namespace Mopsis\Core;

abstract class Registry
{
	protected static $data = null;

	public static function load(...$configFiles)
	{
		if (self::$data !== null) {
			throw new \Exception('registry already loaded');
		}

		self::$data = [];

		foreach ($configFiles as $configFile) {
			self::loadFile($configFile);
		}

		self::defineConstants();
	}

	public static function has($path)
	{
		return self::get($path) !== null;
	}

	public static function get($path)
	{
		$current = &self::$data;

		foreach (explode('/', $path) as $key) {
			$current = &$current[$key];
		}

		return $current;
	}

	public static function set($path, $value)
	{
		$current = &self::$data;

		foreach (explode('/', $path) as $key) {
			$current = &$current[$key];
		}

		$current = $value;
	}

	protected static function loadFile($configFile)
	{
		if (!file_exists($configFile)) {
			$configFile = APPLICATION_PATH . '/' . $configFile;
		}

		if (!file_exists($configFile)) {
			throw new \Exception('configuration file "' . $configFile . '" not found');
		}

		self::$data = array_merge_recursive(self::$data, (include $configFile));
	}

	protected static function defineConstants()
	{
		foreach (self::$data as $category => $entries) {
			if (!isset($entries['@sticky'])) {
				continue;
			}

			unset(self::$data[$category]['@sticky']);
			unset($entries['@sticky']);

			foreach ($entries as $key => $value) {
				if (!define(strtoupper($category . '_' . $key), $value)) {
					throw new \Exception('constant ' . strtoupper($category . '_' . $key) . ' cannot be defined');
				}
			}
		}
	}
}
