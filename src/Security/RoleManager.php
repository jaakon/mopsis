<?php namespace Mopsis\Security;

use Mopsis\Contracts\Hierarchical;
use Mopsis\Contracts\Model;
use Mopsis\Contracts\Role;
use Mopsis\Core\Cache;

class RoleManager
{
	protected static $roles;

	public static function init()
	{
		if (!isset(static::$roles)) {
			static::loadRoles();
		}
	}

	public static function isAllowedTo(Role $role, $action, $object, $instance)
	{
		static::init();

		if (!static::$roles[$role->getKey()][$action][$object]) {
			return false;
		}

		$constraint = $role->getConstraint();

		if (!$constraint) {
			return true;
		}

		if ($instance === null) {
			return true;
		}

		if (!($instance instanceof Model)) {
			return false;
		}

		return static::instanceMeetsConstraint($instance, $constraint);
	}

	protected static function instanceMeetsConstraint(Model $instance, $constraint)
	{
		if ((string) $instance === (string) $constraint) {
			return true;
		}

		if ($instance instanceof Hierarchical) {
			return static::instanceMeetsConstraint($instance->ancestor, $constraint);
		}

		return false;
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
