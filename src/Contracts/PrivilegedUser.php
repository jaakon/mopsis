<?php namespace Mopsis\Contracts;

interface PrivilegedUser
{
	public function may($actionOnObject, $objectToAccess = null);
}
