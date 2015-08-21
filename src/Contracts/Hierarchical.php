<?php namespace Mopsis\Contracts;

interface Hierarchical
{
	public function getAncestorAttribute();
}

trait HierarchicalTrait
{
	public function getAncestorAttribute()
	{
		return isset($this->ancestor) ? $this->{$this->ancestor} : false;
	}
}
