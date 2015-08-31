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

		public static function create($type, $entity, array $parameters = null)
		{
			$format = static::make('classFormats')[$type];

			if ($format === null) {
				throw new \UnexpectedValueException('invalid type "' . $type . '" for entity "' . $entity . '"');
			}

			list($module, $domain, $subtype) = explode('\\', $entity);

			$replacements = array_filter([
				'{{MODULE}}'  => $module,
				'{{DOMAIN}}'  => $domain,
				'{{SUBTYPE}}' => $subtype
			]);

			$class = str_replace(array_keys($replacements), array_values($replacements), $format);

			if (preg_match('/\{\{(\w+)\}\}/', $class, $m)) {
				throw new \InvalidArgumentException('value for placeholder "' . $m[1] . '" for type "' . $type . '" is missing');
			}

			if (!class_exists($class)) {
				throw new \DomainException('value for placeholder "' . $m[1] . '" for type "' . $type . '" is missing');
			}

			return static::make($class, $parameters);
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
		public static function create($entity)
		{
			return \Mopsis\Core\App::build($entity);
		}

		public static function make($type, array $parameters = null)
		{
			return \Mopsis\Core\App::make($type, $parameters);
		}

		public static function set($name, $value)
		{
			\Mopsis\Core\App::set($name, $value);
		}
	}
}
