<?php namespace Mopsis\Core;

use Mopsis\Contracts\Hierarchical;
use Mopsis\Contracts\Model;

abstract class PrivilegedUser extends User
{
	public function roles()
	{
		return $this->hasMany('App\Roles\RoleModel')->orderBy('constraint_id');
	}

	public function may($actionOnObject, $objectToAccess = null)
	{
		foreach ($this->roles as $role) {
			if (!Security::isRoleAllowedTo($role->key, $actionOnObject)) {
				continue;
			}

			if (!$role->constraint->exists) {
				return true;
			}

			if (func_num_args() === 1) {
				return true;
			}

			if (!($objectToAccess instanceof Model)) {
				throw new \Exception('cannot determine privileges without a target object');
			}

			$object = $objectToAccess;

			while ((string)$role->constraint !== (string)$object // get_class() vs. identical classes in hierarchy
				&& $object instanceof Hierarchical
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
