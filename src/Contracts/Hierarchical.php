<?php namespace Mopsis\Contracts;

interface Hierarchical
{
	public function getAncestorAttribute();
}

trait Hierarchical
{
	public function getAncestorAttribute()
	{
		return isset($this->ancestor) ? $this->{$this->ancestor} : false;
	}
}
