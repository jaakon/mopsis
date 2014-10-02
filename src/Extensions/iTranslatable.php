<?php namespace Mopsis\Extensions;

interface iTranslatable
{
	public function __($keyword, $args);
}

trait iTranslatableTrait
{
	public function __($keyword, $args)
	{
		return __($keyword, getClassName($this), array_values($args));
	}
}
