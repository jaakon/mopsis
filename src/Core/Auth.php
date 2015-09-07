<?php namespace Mopsis\Core;

use Illuminate\Contracts\Validation\ValidationException;
use Mopsis\Contracts\User;

class Auth
{
	protected static $user;

	public static function attempt(array $credentials = [], $remember = false, $login = true)
	{
		$userClass = App::get('User');
		$password  = array_pull($credentials, 'password');

		if (!($user = $userClass::find($credentials))) {
			return false;
		}

		// Update passwords without salt (set manually in database)
		if ($user->password === sha1($password)) {
			$user->password = sha1($user->id . config('app.key') . $password);
		}

		if ($user->password !== sha1($user->id . config('app.key') . $password)) {
			return false;
		}

		if ($login) {
			static::login($user, $remember);
		}

		return true;
	}

	public static function check()
	{
		return static::user()->exist;
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

			throw new ValidationException('user has no access');
		}

		if (static::user()->may($permission, $model)) {
			return true;
		}

		if ($redirect) {
			redirect($redirect);
		}

		throw new ValidationException('user has no "' . $permission . '" permission for model "' . $model . '"');
	}

	public static function login(User $user, $remember = false)
	{
		static::$user     = $user;
		$_SESSION['user'] = (string)$user->token;

		if ($remember) {
			app('Cookie')->forever('user', $user->hash);
		}
	}

	public static function logout()
	{
		app('Cookie')->delete('user');
		$_SESSION = [];
		session_destroy();
	}

	public static function user()
	{
		if (static::$user === null) {
			static::$user = static::autoload();
		}

		return static::$user;
	}

	protected static function autoload()
	{
		$userClass = App::get('User');

		if (isset($_SESSION['user'])) {
			try {
				return $userClass::unpack($_SESSION['user']);
			} catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
				unset($_SESSION['user']);
			}
		}

		if (isset($_COOKIE['user'])) {
			try {
				$user = $userClass::unpack($_COOKIE['user']);
				$_SESSION['user'] = (string)$user->token;

				return $user;
			} catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
				app('Cookie')->delete('user');
			}
		}

		return new $userClass;
	}
}
