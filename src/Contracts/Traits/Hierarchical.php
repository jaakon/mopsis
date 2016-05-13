<?php
namespace Mopsis\Contracts\Traits;

trait Hierarchical
{
    public function getAncestorAttribute()
    {
        return isset($this->ancestor) ? $this->{$this->ancestor} : false;
    }
}
