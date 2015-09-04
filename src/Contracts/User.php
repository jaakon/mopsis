<?php namespace Mopsis\Contracts;

interface User
{
	public static function autoLoad();
	public static function authenticate($query, $values, $password, $permanent = false, $checkPassword = false);
	public static function login(\Mopsis\Contracts\User $user, $permanent);
	public static function logout();
}
