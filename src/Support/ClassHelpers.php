<?php namespace Mopsis\Support;

use Mopsis\Core\App;

class ClassHelpers
{
	public static function controller($className)
	{
		return App::build('Controller', $className);
	}

	public static function model($className)
	{
		return App::build('Model', $className);
	}
}
