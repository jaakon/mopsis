<?php namespace Mopsis\Contracts;

interface Translatable
{
	public function __($key, array $replace = []);
}
