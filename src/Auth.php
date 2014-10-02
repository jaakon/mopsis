<?php namespace Mopsis;

class Auth
{
	private static $_user;

	public static function login(Core\User $user)
	{
		self::$_user = $user;
	}

	public static function user()
	{
		return self::$_user ?: self::$_user = \Models\User::autoload();
	}

	public static function checkAccess($permission, $model = null, $redirect = null)
	{
		if (is_bool($permission)) {
			if ($permission) {
				return true;
			}

			if ($redirect) {
				redirect($redirect);
			}

			throw new \AccessException('user has no access');
		}

		if (self::user()->isAllowedTo($permission, $model)) {
			return true;
		}

		if ($redirect) {
			redirect($redirect);
		}

		throw new \AccessException('user has no "'.$permission.'" permission for model "'.$model.'"');
	}
}
