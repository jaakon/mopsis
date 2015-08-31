<?php

use Mopsis\Core\App;
use Mopsis\Support\ArrayHelpers;
use Mopsis\Support\ClassHelpers;
use Mopsis\Support\MiscHelpers;
use Mopsis\Support\ObjectHelpers;
use Mopsis\Support\PathHelpers;
use Mopsis\Support\StringHelpers;

function __($key, array $replace = [])
{
	return App::make('Translator')->get($key, $replace);
}

function app($type)
{
	return App::make($type);
}

function array_concat(array $array, ...$values)
{
	return ArrayHelpers::concat($array, ...$values);
}

function array_diff_values(array $array1, array $array2)
{
	return ArrayHelpers::diffValues($array1, $array2);
}

function array_value($array, $key)
{
	return ArrayHelpers::value($array, $key);
}

function array_wrap($data)
{
	return ArrayHelpers::wrap($data);
}

function camelCase($string)
{
	return StringHelpers::camelCase($string);
}

function debug(...$args)
{
	return MiscHelpers::debug(...$args);
}

function getClosestMatch($input, $words)
{
	return StringHelpers::getClosestMatch($input, $words);
}

function object_to_array($object)
{
	return ObjectHelpers::toArray($object);
}

function object_merge(stdClass $object1, stdClass ...$objects)
{
	return ObjectHelpers::merge($object1, ...$objects);
}

function is_utf8($string)
{
	return StringHelpers::isUtf8($string);
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

function strip_invalid_chars($string, $charlist = null)
{
	return StringHelpers::stripInvalidChars($string, $charlist);
}

function vnsprintf($format, array $args)
{
	return StringHelpers::vnsprintf($format, $args);
}
