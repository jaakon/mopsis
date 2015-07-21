<?php namespace Mopsis;

use Mopsis\Core\User;
use Mopsis\Exceptions\AccessException;

class Auth
{
	protected static $user;

	public static function login(User $user)
	{
		self::$user = $user;
	}

	public static function user()
	{
		if (self::$user === null) {
			$model = \Mopsis\Core\App::make('User');
			self::$user = $model::autoload();
		}

		return self::$user;
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

			throw new AccessException('user has no access');
		}

		if (self::user()->may($permission, $model)) {
			return true;
		}

		if ($redirect) {
			redirect($redirect);
		}

		throw new AccessException('user has no "' . $permission . '" permission for model "' . $model . '"');
	}
}
