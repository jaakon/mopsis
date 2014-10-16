<?php namespace Mopsis\Core;

class Security
{
	public static function generateToken()
	{
		$key   = substr(sha1(uniqid(rand(), true)), 0, 8);
		$value = base64_encode(openssl_random_pseudo_bytes(32));

		$_SESSION['csrf'] = ['key' => $key, 'value' => $value];

		return (object) $_SESSION['csrf'];
	}

	public static function getPrivilegesForRole($role)
	{
		return self::_loadRoles()[$role] ?: [];
	}

	private static function _loadRoles()
	{
		$item  = \App::make('cache')->getItem('user_roles');
		$value = $item->get(\Stash\Invalidation::OLD);

		if ($item->isMiss()) {
			if (!Registry::has('roles')) {
				throw new \Exception('configuration for roles is missing');
			}

			$item->lock();
			$value = [];

			foreach (Registry::get('roles') as $role => $privileges) {
				$data = [];

				foreach ($privileges as $objects => $actions) {
					foreach (explode(',', $objects) as $object) {
						foreach (explode(',', $actions) as $action) {
							$data[] = $action.'_'.$object;
						}
					}
				}

				$value[$role] = array_unique($data);
			}

			$item->set($value, 3600);
		}

		return $value;
	}
}
