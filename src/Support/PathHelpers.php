<?php namespace Mopsis\Support;

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
}
