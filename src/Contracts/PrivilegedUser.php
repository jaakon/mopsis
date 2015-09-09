<?php namespace Mopsis\Contracts;

interface PrivilegedUser
{
	public function roles();

	public function may($actionOnObject, $objectToAccess = null);
}
