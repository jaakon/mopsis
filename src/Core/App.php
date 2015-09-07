<?php namespace Mopsis\Core;

use Interop\Container\ContainerInterface as ContainerInterface;

class App
{
	protected static $container;

	public static function initialize(ContainerInterface $container)
	{
		static::$container = $container;
	}

	public static function build($type, $name)
	{
		$format = static::$container->get('classFormats')[$type];

		if ($format === null) {
			throw new \UnexpectedValueException('invalid type "' . $type . '" for entity "' . $name . '"');
		}

		list($module, $domain, $subtype) = explode('\\', $name);

		$replacements = array_filter([
			'{{MODULE}}'  => $module,
			'{{DOMAIN}}'  => $domain,
			'{{SUBTYPE}}' => $subtype
		]);

		$class = str_replace(array_keys($replacements), array_values($replacements), $format);

		if (preg_match('/\{\{(\w+)\}\}/', $class, $m)) {
			throw new \InvalidArgumentException('value for placeholder "' . $m[1] . '" for type "' . $type . '" is missing');
		}

		if (!static::$container->has($class)) {
			throw new \DomainException('class "' . $class . '" for type "' . $type . '" not found');
		}

		return $class;
	}

	public static function create($type, $name, array $parameters = null)
	{
		return static::$container->make(static::build($type, $name), $parameters);
	}

	public static function getInstance()
	{
		return static::$container;
	}

	public static function __callStatic($method, $args)
	{
		return static::$container->$method(...$args);
	}
}

/*
namespace {

	class App
	{
		public static function __callStatic($method, $args)
		{
			return \Mopsis\Core\App::$method(...$args);
		}
	}
}
*/