<?php namespace Mopsis\Validation\Specification;

interface iRequestSpecification
{
	public function isSatisfiedBy(\Mopsis\Validation\ValidationCoordinator $coordinator);
	public function getValidatedField();
}
