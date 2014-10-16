<?php namespace Mopsis\Core;

abstract class PrivilegedUser extends \Mopsis\Core\User
{
	public function roles()
	{
		return $this->hasMany('\Models\Role');
	}

	public function isAllowedTo($privilege, $objectToAccess = null)
	{
		if (!($containments = $this->privileges[strtolower($privilege)])) {
			return false;
		}

		if ($containments === true || empty($objectToAccess)) {
			return true;
		}

		foreach ($containments as $containment) {
			$object = $objectToAccess;

			while (get_class($object) !== get_class($containment) && $object instanceof \Mopsis\Extensions\iHierarchical) {
				$object = $object->ancestor;
			}

			if ((string) $containment === (string) $object) {
				return true;
			}
		}

		return false;
	}

	public function getPrivilegesAttribute()
	{
		$result = [];

		foreach ($this->roles as $role) {
			foreach (Security::getPrivilegesForRole($role->key) as $privilege) {
				if (!$role->containment) {
					$result[$privilege] = true;
				}

				if ($result[$privilege] === true) {
					continue;
				}

				if (!is_array($result[$privilege])) {
					$result[$privilege] = [];
				}

				$result[$privilege][] = $role->containment;
			}
		}

		return $result;
	}
}
