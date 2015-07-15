<?php namespace Mopsis\Filter\Rule\Validate;

class Optional
{
	public function __invoke($subject, $field)
	{
		return true;
	}
}
