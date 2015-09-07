<?php namespace Mopsis\Contracts;

interface User
{
	public static function authenticate($query, $values, $password, $permanent = false, $checkPassword = false);
}
