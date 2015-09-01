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

		public static function build($type, $entity)
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

			if (!static::has($class)) {
				throw new \DomainException('class "' . $class . '" for type "' . $type . '" not found');
			}

			return $class;
		}

		public static function create($type, $entity, array $parameters = null)
		{
			return static::make(static::build($type, $entity), $parameters);
		}

		public static function make($entity, array $parameters = null)
		{
			switch ($entity) {
				case 'db':
					return static::$container->get(Database::class)->getConnection();
				default:
					return is_array($parameters) ? static::$container->make($entity, $parameters) : static::$container->get($entity);
			}
		}

		public static function set($entity, $value)
		{
			static::$container->set($entity, $value);
		}
	}
}

namespace {

	class App
	{
		public static function __callStatic($method, $args)
		{
			return \Mopsis\Core\App::$method(...$args);
		}
	}
}
