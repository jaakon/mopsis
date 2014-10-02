<?php namespace Mopsis\Core;

abstract class Registry
{
	private static $_data = null;

	public static function load($configFile)
	{
		if (self::$_data !== null) {
			throw new \Exception('registry already loaded');
		}

		self::$_data = self::_loadFile($configFile);

		if ($_SERVER['SITE'] && self::has($_SERVER['SITE'], 'sites')) {
			self::$_data = array_replace_recursive(self::$_data, parse_ini_file(pathinfo($configFile, PATHINFO_DIRNAME).'/'.self::get($_SERVER['SITE'], 'sites'), true));
			self::$_data['*core']['site'] = $_SERVER['SITE'];
			unset(self::$_data['sites']);
		}

		self::_defineConstants();
	}

	public static function has($path)
	{
		return self::get($path) !== null;
	}

	public static function get($path)
	{
		$current = &self::$_data;

		foreach (explode('/', $path) as $key) {
			$current = &$current[$key];
		}

		return $current;
	}

	public static function set($path, $value)
	{
		$current = &self::$_data;

		foreach (explode('/', $path) as $key) {
			$current = &$current[$key];
		}

		$current = $value;
	}

	private static function _loadFile($configFile)
	{
		if (!file_exists($configFile)) {
			throw new \Exception('configuration file "'.$configFile.'" not found');
		}

		return (include $configFile);
	}

	private static function _defineConstants()
	{
		foreach (self::$_data as $category => $entries) {
			if (!isset($entries['@sticky'])) {
				continue;
			}

			unset(self::$_data[$category]['@sticky']);
			unset($entries['@sticky']);

			foreach ($entries as $key => $value) {
				if (!define(strtoupper($category.'_'.$key), $value)) {
					throw new \Exception('constant '.strtoupper($category.'_'.$key).' cannot be defined');
				}
			}
		}
	}
}
