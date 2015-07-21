<?php namespace Mopsis\Extensions;

trait HierarchicalTrait
{
	public function getAncestorAttribute()
	{
		return $this->ancestor ? $this->{$this->ancestor} : false;
	}
}

interface HierarchicalInterface
{
	public function getAncestorAttribute();
}
