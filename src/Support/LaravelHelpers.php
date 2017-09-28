<?php
namespace Mopsis\Support;

use Mopsis\Core\App;

class LaravelHelpers
{
    public static function app($name = null, array $parameters = null)
    {
        if ($name === null) {
            return new App(); //::getInstance();
        }

        if (preg_match('/^(\w+):(\w+\\\\\w+)$/', $name, $m)) {
            $name = App::build($m[1], $m[2]);
        }

        if ($parameters === null) {
            return App::get($name);
        }

        return App::make($name, $parameters);
    }

    public static function config($key = null, $default = null)
    {
        if ($key === null) {
            return App::get('config');
        }

        if (is_array($key)) {
            return App::get('config')->set($key);
        }

        return App::get('config')->get($key, $default);
    }

    public static function env($key, $default = null)
    {
        $value = getenv($key, true) ?: getenv($key);

        if ($value === false) {
            return value($default);
        }

        switch (strtolower(trim($value, '()'))) {
            case 'true':
                return true;
            case 'false':
                return false;
            case 'empty':
                return '';
            case 'null':
                return;
        }

        if (preg_match('/^"(.+)"$/', $value, $m)) {
            return $m[1];
        }

        return $value;
    }

    public static function event($event)
    {
        return;
    }
}
