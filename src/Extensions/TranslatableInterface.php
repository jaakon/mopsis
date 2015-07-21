<?php namespace Mopsis\Extensions;

trait TranslatableTrait
{
	public function __($key, array $replace = [])
	{
		return;
	}
}

interface TranslatableInterface
{
	public function __($key, array $replace = []);
}
