<?php namespace Mopsis\Security;

use Mopsis\Core\Cache;

class RoleManager
{
	protected static $roles;

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
			$roles = config('roles');

			if (!count($roles)) {
				throw new \Exception('configuration for roles is missing');
			}

			foreach ($roles as $role => $privileges) {
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
