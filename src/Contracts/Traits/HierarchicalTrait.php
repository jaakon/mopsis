<?php namespace Mopsis\Contracts\Traits;

trait HierarchicalTrait
{
	public function getAncestorAttribute()
	{
		return isset($this->ancestor) ? $this->{$this->ancestor} : false;
	}
}
