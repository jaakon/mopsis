<?php

namespace Mopsis\Core {

	use Interop\Container\ContainerInterface as ContainerInterface;

	class App
	{
		private static $container;

		public static function initialize(ContainerInterface $container)
		{
			static::$container = $container;
		}

		public static function build($class)
		{
			if (!preg_match('/^(\w+)\\\(\w+)$/', $class, $m)) {
				throw new \Exception('invalid parameter for build call: ' . $class);
			}

			return static::make(sprintf('\App\%1$s\%2$sController', $m[1], $m[2]));
/*
			if (!preg_match('/^(.+?)\\\(.+?)\\\(.+?)$/', $class, $m)) {
				throw new \Exception('invalid parameter for build call: ' . $class);
			}

			return static::make(sprintf('\App\%1$s\%2$s\%1$s%3$s%4$s', $m[1], $m[2], $m[3], str_replace('Domain', '', $m[2])));
*/
		}

		public static function make($type, array $parameters = null)
		{
			switch ($type) {
				case 'db':
					return static::$container->get(Database::class)->getConnection();
				default:
					return is_array($parameters) ? static::$container->make($type, $parameters) : static::$container->get($type);
			}
		}

		public static function set($name, $value)
		{
			static::$container->set($name, $value);
		}
	}
}

namespace {

	class App
	{
		public static function build($class)
		{
			return \Mopsis\Core\App::build($class);
		}

		public static function make($type, array $parameters = null)
		{
			switch ($type) {
				case 'config':
					return new \Libraries\Config();
				default:
					return \Mopsis\Core\App::make($type, $parameters);
			}
		}

		public static function set($name, $value)
		{
			\Mopsis\Core\App::set($name, $value);
		}
	}
}
