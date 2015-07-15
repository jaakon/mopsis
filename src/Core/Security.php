<?php namespace Mopsis\Core;

use Mopsis\Core\Cache;

class Security
{
	protected static $roles;

	public static function generateToken()
	{
		$key   = substr(sha1(uniqid(rand(), true)), 0, 8);
		$value = base64_encode(openssl_random_pseudo_bytes(32));

		$_SESSION['csrf'] = ['key' => $key, 'value' => $value];

		return (object) $_SESSION['csrf'];
	}

	public static function isRoleAllowedTo($role, $actionOnObject)
	{
		if (!isset(static::$roles)) {
			static::loadRoles();
		}

		list($action, $object) = explode('_', $actionOnObject);

		return static::$roles[$role][$action][$object];
	}

	protected static function loadRoles()
	{
		static::$roles = Cache::get('user_roles', function () {
			if (!Registry::has('roles')) {
				throw new \Exception('configuration for roles is missing');
			}

			$roles = [];

			foreach (Registry::get('roles') as $role => $privileges) {
				$roles[$role] = [];

				foreach ($privileges as $actions => $objects) {
					foreach (explode(',', $actions) as $action) {
						$roles[$role][$action] = $roles[$role][$action] ?: [];

						foreach (explode(',', $objects) as $object) {
							$roles[$role][$action][$object] = true;
						}
					}
				}
			}

			return $roles;
		});
	}
}
