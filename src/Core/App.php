<?php

namespace Mopsis\Core
{
	class App
	{
		private static $_container;

		public static function initialize(\DI\ContainerInterface $container)
		{
			static::$_container = $container;
		}

		public static function make($type, array $parameters = null)
		{
			switch ($type) {
				case 'db':
					return static::$_container->get('database')->getConnection();
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
