<?php namespace Mopsis\Contracts\Traits;

use Mopsis\Core\Auth;

trait UserTrait
{
	public static function authenticate($query, $values, $password, $remember = false, $checkPassword = false)
	{
		if (!Auth::attempt([], $remember)) {
			return false;
		}

		if ($checkPassword && !isPasswordSafe($password)) {
			Auth::user()->password = null;
		}

		return true;
	}
}
