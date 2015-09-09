<?php namespace Mopsis\Contracts\Traits;

use Mopsis\Contracts\Hierarchical;
use Mopsis\Contracts\Model;
use Mopsis\Security\RoleManager;

trait PrivilegedUserTrait
{
	public function roles()
	{
		throw new \Exception('$user->roles is not defined!');
	}

	public function may($actionOnObject, $objectToAccess = null)
	{
		foreach ($this->roles as $role) {
			if (!RoleManager::isRoleAllowedTo($role->key, $actionOnObject)) {
				continue;
			}

			if (!$role->constraint->exists) {
				return true;
			}

			if (func_num_args() === 1) {
				return true;
			}

			if (!($objectToAccess instanceof Model)) {
				return false;
//				throw new \Exception('cannot determine privileges without a target object');
			}

			$object = $objectToAccess;

			while ((string)$role->constraint !== (string)$object // get_class() vs. identical classes in hierarchy
				&& $object instanceof Hierarchical) {
				$object = $object->ancestor;
			}

			if ((string)$role->constraint === (string)$object) {
				return true;
			}
		}

		return false;
	}
}
