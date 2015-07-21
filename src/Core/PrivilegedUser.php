<?php namespace Mopsis\Core;

abstract class PrivilegedUser extends \Mopsis\Core\User
{
	public function roles()
	{
		return $this->hasMany('App\Roles\RoleModel')->orderBy('constraint_id');
	}

	public function may($actionOnObject, \Mopsis\Eloquent\Model $objectToAccess = null)
	{
		foreach ($this->roles as $role) {
			if (!Security::isRoleAllowedTo($role->key, $actionOnObject)) {
				continue;
			}

			if (!$role->constraint->exists) {
				return true;
			}

			if ($objectToAccess === null) { // empty() for Collections?
				throw new \Exception('cannot determine privileges without a target object');
			}

			$object = $objectToAccess;

			while ((string)$role->constraint !== (string)$object // get_class() vs. identical classes in hierarchy
				&& $object instanceof \Mopsis\Extensions\iHierarchical
			) {
				$object = $object->ancestor;
			}

			if ((string)$role->constraint === (string)$object) {
				return true;
			}
		}

		return false;
	}
}
