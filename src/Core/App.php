<?php

namespace Mopsis\Core
{
	use Interop\Container\ContainerInterface as ContainerInterface;

	class App
	{
		private static $_container;

		public static function initialize(ContainerInterface $container)
		{
			static::$_container = $container;
		}

		public static function make($type, array $parameters = null)
		{
			switch ($type) {
				case 'db':
					return static::$_container->get('Database')->getConnection();
				default:
					return is_array($parameters) ? static::$_container->make($type, $parameters) : static::$_container->get($type);
			}
		}
	}
}

namespace
{
	class App
	{
		public static function make($type, array $parameters = null)
		{
			switch ($type) {
				case 'config':
					return new \Config();
				default:
					return \Mopsis\Core\App::make($type, $parameters);
			}
		}
	}
}
