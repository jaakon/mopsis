<?php
namespace Mopsis\Core;

use DI\Container;

/**
 * @method static make($name, array $parameters = [])
 */
class App
{
    protected static $container;

    public static function __callStatic($method, $args)
    {
        return static::getInstance()->$method(...$args);
    }

    public static function build($type, $name)
    {
        $class = static::getFullyQualifiedName($type, $name);

        if (!static::getInstance()->has($class)) {
            throw new \DomainException('class "' . $class . '" for type "' . $type . '" not found');
        }

        return $class;
    }

    public static function create($type, $name, array $parameters = [])
    {
        return static::getInstance()->make(static::build($type, $name), $parameters);
    }

    public static function get(...$args)
    {
        return static::getInstance()->get(...$args);
    }

    public static function getFullyQualifiedName($type, $name)
    {
        $format = static::getInstance()->get('classFormats')[$type];

        if ($format === null) {
            throw new \UnexpectedValueException('unknown type "' . $type . '" for entity "' . $name . '"');
        }

        list($module, $subtype) = explode('\\', $name);

        $replacements = array_filter([
            '{{MODULE}}'  => $module,
            '{{SUBTYPE}}' => $subtype
        ]);

        $class = str_replace(array_keys($replacements), array_values($replacements), $format);

        if (preg_match('/\{\{(\w+)\}\}/', $class, $m)) {
            throw new \InvalidArgumentException('value for placeholder "' . $m[1] . '" for type "' . $type . '" is missing');
        }

        return $class;
    }

    public static function getInstance(): Container
    {
        return static::$container;
    }

    public static function has(...$args)
    {
        return static::getInstance()->has(...$args);
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

    public static function initialize(Container $container)
    {
        static::$container = $container;
    }
}
