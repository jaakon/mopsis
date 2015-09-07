<?php namespace Mopsis\Contracts\Traits;

trait UserTrait
{
	public static function authenticate($query, $values, $password, $remember = false, $checkPassword = false)
	{
		if (!\Mopsis\Core\Auth::attempt([], $remember)) {
			return false;
		}

		if ($checkPassword && !isPasswordSafe($password)) {
			\Mopsis\Core\Auth::user()->password = null;
		}

		return true;
	}
}
