<?php namespace Mopsis\Core;

use Illuminate\Config\Repository;

class Config extends Repository
{
	/** @Override */
	public function get($key, $default = null)
	{
		switch ($key) {
			case 'sluggable':
				return include APPLICATION_PATH . '/vendor/cviebrock/eloquent-sluggable/config/sluggable.php';
			case 'taggable.delimiters':
				$config = include APPLICATION_PATH . '/vendor/cviebrock/eloquent-taggable/config/taggable.php';

				return $config['delimiters'];
			case 'taggable.normalizer':
				$config = include APPLICATION_PATH . '/vendor/cviebrock/eloquent-taggable/config/taggable.php';

				return $config['normalizer'];
		}

		return parent::get($key, $default);
	}

	public function load(...$configFiles)
	{
		if (count($this->items)) {
			throw new \Exception('config already initialized');
		}

		foreach ($configFiles as $configFile) {
			$this->loadFile($configFile);
		}
	}

	protected function loadFile($configFile)
	{
		if (!file_exists($configFile)) {
			throw new \Exception('configuration file "' . $configFile . '" not found');
		}

		$this->set(array_dot(include $configFile));
	}
}
