<?php namespace Mopsis\Contracts\Traits;

trait PrivilegedUserTrait
{
	public function roles()
	{
		throw new \Exception('$user->roles is not defined!');
	}

	public function may($actionOnObject, $objectToAccess = null)
	{
		foreach ($this->roles as $role) {
			if (!\Mopsis\Security\RoleManager::isRoleAllowedTo($role->key, $actionOnObject)) {
				continue;
			}

			if (!$role->constraint->exists) {
				return true;
			}

			if (func_num_args() === 1) {
				return true;
			}

			if (!($objectToAccess instanceof \Mopsis\Contracts\Model)) {
				return false;
//				throw new \Exception('cannot determine privileges without a target object');
			}

			$object = $objectToAccess;

			while ((string)$role->constraint !== (string)$object // get_class() vs. identical classes in hierarchy
				&& $object instanceof \Mopsis\Contracts\Hierarchical
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
