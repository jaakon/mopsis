<?php namespace Mopsis\Core;

abstract class PrivilegedUser extends \Mopsis\Core\User
{
	public function roles()
	{
		return $this->hasMany('\Models\Role');
	}

	public function isAllowedTo($privilege, $objectToAccess = null)
	{
		if (!($constraints = $this->privileges[strtolower($privilege)])) {
			return false;
		}

		if ($constraints === true || empty($objectToAccess)) {
			return true;
		}

		foreach ($constraints as $constraint) {
			$object = $objectToAccess;

			while (get_class($object) !== get_class($constraint) && $object instanceof \Mopsis\Extensions\iHierarchical) {
				$object = $object->ancestor;
			}

			if ((string) $constraint === (string) $object) {
				return true;
			}
		}

		return false;
	}

	public function getPrivilegesAttribute()
	{
		$result = [];

		foreach ($this->roles as $role) {
			foreach (self::_getPrivilegesForRole((string) $role->key) as $privilege) {
				if (true || !$role->constraint) {
					$result[$privilege] = true;
				}

				if ($result[$privilege] === true) {
					continue;
				}

				if (!is_array($result[$privilege])) {
					$result[$privilege] = [];
				}

				$result[$privilege][] = $role->constraint;
			}
		}

		return $result;
	}

	private static function _getPrivilegesForRole($role)
	{
		return self::_loadRolesFromRegistry()[$role] ?: [];
	}

	private static function _loadRolesFromRegistry()
	{
		if (\Mopsis\Core\Registry::has('rolesExtracted')) {
			return \Mopsis\Core\Registry::get('rolesExtracted');
		}

		if (!\Mopsis\Core\Registry::has('roles')) {
			throw new \Exception('configuration for roles is missing');
		}

		$results = [];

		foreach (\Mopsis\Core\Registry::get('roles') as $role => $privileges) {
			$data = [];

			foreach ($privileges as $objects => $actions) {
				foreach (explode(',', $objects) as $object) {
					foreach (explode(',', $actions) as $action) {
						$data[] = $action.'_'.$object;
					}
				}
			}

			$results[$role] = array_unique($data);
		}

		\Mopsis\Core\Registry::set('rolesExtracted', $results);

		return $results;
	}
}
