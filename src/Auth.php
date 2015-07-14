<?php namespace Mopsis;

class Auth
{
	protected static $user;

	public static function login(Core\User $user)
	{
		self::$user = $user;
	}

	public static function user()
	{
		return self::$user ?: self::$user = \Models\User::autoload();
	}

	public static function checkAccess($permission, $model = null, $redirect = null)
	{
		if ((is_bool($permission) && $permission) || self::user()->isAllowedTo($permission, $model)) {
			return true;
		}

		if ($redirect) {
			redirect($redirect);
		}

		throw new \AccessException($model ? 'user has no "'.$permission.'" permission for model "'.$model.'"' : 'user has no permission');
	}
}
