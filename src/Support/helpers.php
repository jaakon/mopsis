<?php

use Mopsis\Core\App;
use Mopsis\Support\ArrayHelpers;
use Mopsis\Support\ClassHelpers;
use Mopsis\Support\LaravelHelpers;
use Mopsis\Support\MiscHelpers;
use Mopsis\Support\ObjectHelpers;
use Mopsis\Support\PathHelpers;
use Mopsis\Support\StringHelpers;

function __($key, array $replace = [])
{
    return App::get('Translator')->get($key, $replace);
}

function app($name = null, array $parameters = null)
{
    return LaravelHelpers::app($name, $parameters);
}

function arrayConcat(array $array, ...$values)
{
    return ArrayHelpers::concat($array, ...$values);
}

function arrayDiffValues(array $array1, array $array2)
{
    return ArrayHelpers::diffValues($array1, $array2);
}

function arrayDot($array, $prepend = '')
{
    return ArrayHelpers::dot($array, $prepend);
}

function arrayTrim(array $array, callable $callback = null)
{
    return ArrayHelpers::trim($array, $callback);
}

function arrayValue($array, $key)
{
    return ArrayHelpers::value($array, $key);
}

function arrayWrap($data)
{
    return ArrayHelpers::wrap($data);
}

function between($value, $min, $max)
{
    return MiscHelpers::between($value, $min, $max);
}

function config($key = null, $default = null)
{
    return LaravelHelpers::config($key, $default);
}

function controller($className)
{
    return ClassHelpers::controller($className);
}

function debug(...$args)
{
    echo MiscHelpers::debug(...$args);
}

function duration($hours)
{
    return StringHelpers::duration($hours);
}

function env($key, $default = null)
{
    return LaravelHelpers::env($key, $default);
}

function closestMatch($input, $words)
{
    return StringHelpers::getClosestMatch($input, $words);
}

function isHtml($string)
{
    return StringHelpers::isHtml($string);
}

function isUtf8($string)
{
    return StringHelpers::isUtf8($string);
}

function logger($message = null)
{
    return MiscHelpers::logger($message);
}

function model($className)
{
    return ClassHelpers::model($className);
}

function objectMerge(stdClass $baseObject, stdClass...$objects)
{
    return ObjectHelpers::merge($baseObject, ...$objects);
}

function objectToArray($object)
{
    return ObjectHelpers::toArray($object);
}

function locationChange($uri, $code = 302, $phrase = null)
{
    return MiscHelpers::locationChange($uri, $code, $phrase);
}

function pluralize($count, $singular, $plural = null)
{
    return StringHelpers::pluralize($count, $singular, $plural);
}

function redirect($uri, $code = 302, $phrase = null)
{
    return MiscHelpers::redirect($uri, $code, $phrase);
}

function resolvePath($path)
{
    return PathHelpers::resolve($path);
}

function staticPage($code)
{
    return MiscHelpers::getStaticPage($code);
}

function stripInvalidChars($string, $charlist = null)
{
    return StringHelpers::stripInvalidChars($string, $charlist);
}

function vnsprintf($format, array $args)
{
    return StringHelpers::vnsprintf($format, $args);
}
