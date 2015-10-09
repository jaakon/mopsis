<?php namespace Mopsis\Support;

use Mopsis\Core\App;

class PathHelpers
{
	public static function resolve($path)
	{
		// replace backslashes with forward slashes
		$path = str_replace('\\', '/', $path);

		// resolve /a/b//c => /c
		$path = preg_replace('/.*\/\//', '/', $path);

		// resolve /a/b/./c => /a/b/c
		$path = preg_replace('/(\/\.)+\//', '/', $path);

		// resolve /a/b/../c => /a/c
		$path = preg_replace('/\/\w+\/\.\.\//', '/', $path);

		return $path;
	}

	public static function addLocation($uri)
	{
		if (parse_url($uri, PHP_URL_SCHEME)) {
			return $uri;
		}

		$request = App::get('Aura\Web\Request');

		return $request->url->get(PHP_URL_SCHEME) . '://' . $request->url->get(PHP_URL_HOST)
			. static::resolve(rtrim($request->url->get(PHP_URL_PATH), '/') . '/' . $uri);
	}
}
