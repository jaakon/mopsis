<?php namespace Mopsis\Core;

use Interop\Container\ContainerInterface as ContainerInterface;

class App
{
	protected static $container;

	public static function initialize(ContainerInterface $container)
	{
		static::$container = $container;
	}

	public static function create($type, $name, array $parameters = [])
	{
		return static::getInstance()->make(static::build($type, $name), $parameters);
	}

	public static function getInstance(): ContainerInterface
	{
		return static::$container;
	}

	public static function build($type, $name)
	{
		$class = static::getFullyQualifiedName($type, $name);

		if (!static::getInstance()->has($class)) {
			throw new \DomainException('class "' . $class . '" for type "' . $type . '" not found');
		}

		return $class;
	}

	public static function getFullyQualifiedName($type, $name)
	{
		$format = static::getInstance()->get('classFormats')[$type];

		if ($format === null) {
			throw new \UnexpectedValueException('unknown type "' . $type . '" for entity "' . $name . '"');
		}

		list($module, $domain, $subtype) = explode('\\', $name);

		$replacements = array_filter(['{{MODULE}}' => $module, '{{DOMAIN}}' => $domain, '{{SUBTYPE}}' => $subtype]);

		$class = str_replace(array_keys($replacements), array_values($replacements), $format);

		if (preg_match('/\{\{(\w+)\}\}/', $class, $m)) {
			throw new \InvalidArgumentException('value for placeholder "' . $m[1] . '" for type "' . $type . '" is missing');
		}

		return $class;
	}

	public static function identify($class)
	{
		if (is_object($class)) {
			$class = get_class($class);
		}

		foreach (static::getInstance()->get('classFormats') as $format) {
			$format = preg_replace('/\{\{[A-Z]+\}\}/', '((?:[A-Z][a-z]+)+)', str_replace('\\', '\\\\', $format));
			if (preg_match('/' . $format . '/', $class, $m)) {
				return array_slice($m, 1);
			}
		}

		throw new \DomainException('called class "' . $class . '" cannot be identified');
	}

	public static function get(...$args)
	{
		return static::getInstance()->get(...$args);
	}

	public static function has(...$args)
	{
		return static::getInstance()->has(...$args);
	}

	public static function __callStatic($method, $args)
	{
		return static::getInstance()->$method(...$args);
	}
}
