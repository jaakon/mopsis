<?php namespace Mopsis\Core;

abstract class User extends \Mopsis\Eloquent\Model
{
	protected $isAuthorized = false;

	public static function autoLoad()
	{
		if (isset($_SESSION['user'])) {
			try {
				return static::unpack($_SESSION['user']);
			} catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
				unset($_SESSION['user']);
			}
		}

		if (isset($_COOKIE['user'])) {
			try {
				$user = static::unpack($_COOKIE['user']);
				$_SESSION['user'] = (string) $user->token;
				return $user;
			} catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
				setcookie('user', '', time() - 3600, '/');
			}
		}

		return new static;
	}

	public static function authenticate($query, $values, $password, $permanent = false, $checkPassword = false)
	{
		$class = get_called_class();

		if (!($user = $class::find($query, $values))) {
			return false;
		}

		// Update old passwords
		if ($user->password === sha1($password)) {
			$user->password = sha1($user->id.CORE_SALT.$password);
		}

		if ($user->password !== sha1($user->id.CORE_SALT.$password)) {
			return false;
		}

		if ($checkPassword && !isPasswordSafe($password)) {
			$user->password = null;
		}

		$class::login($user, $permanent);

		return true;
	}

	public static function login(User $user, $permanent)
	{
		$_SESSION['user'] = (string) $user->token;

		if ($permanent) {
			setcookie('user', $user->hash, time() + 365 * 86400, '/', $_SERVER['HTTP_HOST'], false, true);
		}
	}

	public static function logout()
	{
		setcookie('user', null, time() - 3600, '/', $_SERVER['HTTP_HOST'], false, true);
		unset($_SESSION);
		session_destroy();
	}

	public function authorize($bool)
	{
		$this->isAuthorized = $this->exists && $bool;
	}

	public function isAuthorized()
	{
		return $this->exists && $this->isAuthorized;
	}
}
