<?php

use Mopsis\Core\App;
use Mopsis\Extensions\DI\Definition\Helper\DotNotationDefinitionHelper;
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

function array_concat(array $array, ...$values)
{
	return ArrayHelpers::concat($array, ...$values);
}

function array_diff_values(array $array1, array $array2)
{
	return ArrayHelpers::diffValues($array1, $array2);
}

function array_trim(array $array, callable $callback = null)
{
	return ArrayHelpers::trim($array, $callback);
}

function array_value($array, $key)
{
	return ArrayHelpers::value($array, $key);
}

function array_wrap($data)
{
	return ArrayHelpers::wrap($data);
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
	return MiscHelpers::debug(...$args);
}

function duration($hours)
{
	return StringHelpers::duration($hours);
}

function env($key, $default = null)
{
	return LaravelHelpers::env($key, $default);
}

function getClosestMatch($input, $words)
{
	return StringHelpers::getClosestMatch($input, $words);
}

function is_html($string)
{
	return StringHelpers::isHtml($string);
}

function is_utf8($string)
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

function object_merge(stdClass $object1, stdClass ...$objects)
{
	return ObjectHelpers::merge($object1, ...$objects);
}

function object_to_array($object)
{
	return ObjectHelpers::toArray($object);
}

function pluralize($count, $singular, $plural = null)
{
	return StringHelpers::pluralize($count, $singular, $plural);
}

function redirect($url = null, $responseCode = 302)
{
	return MiscHelpers::redirect($url, $responseCode);
}

function resolve_path($path)
{
	return PathHelpers::resolve($path);
}

function static_page($code)
{
	return MiscHelpers::getStaticPage($code);
}

function strip_invalid_chars($string, $charlist = null)
{
	return StringHelpers::stripInvalidChars($string, $charlist);
}

function vnsprintf($format, array $args)
{
	return StringHelpers::vnsprintf($format, $args);
}
