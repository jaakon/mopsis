<?php
namespace Mopsis\Contracts\Traits;

trait HierarchicalTrait
{
    public function getAncestorAttribute()
    {
        return isset($this->ancestor) ? $this->{$this->ancestor}
            : false;
    }

    public function getUriRecursive()
    {
        if ($this->exists && isset($this->uri)) {
            return $this->uri;
        }

        if ($this->ancestor) {
            return $this->ancestor->getUriRecursive();
        }

        return;
    }
}
