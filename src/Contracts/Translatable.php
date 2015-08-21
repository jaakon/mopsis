<?php namespace Mopsis\Contracts;

interface Translatable
{
	public function __($key, array $replace = []);
}

trait TranslatableTrait
{
	public function __($key, array $replace = [])
	{
		return __($key, $replace);
	}
}
