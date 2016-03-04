<?php
namespace Mopsis\Contracts\Traits;

use Mopsis\Security\RoleManager;

trait PrivilegedUserTrait
{
    public function may($targetAction, $instance = null)
    {
        list($action, $object) = explode('_', $targetAction);

        foreach ($this->roles as $role) {
            if (RoleManager::isAllowedTo($role, $action, $object, $instance)) {
                return true;
            }
        }

        return false;
    }
}
