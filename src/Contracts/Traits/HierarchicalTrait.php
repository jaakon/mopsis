<?php
namespace Mopsis\Contracts\Traits;

use Mopsis\Contracts\Model;

trait HierarchicalTrait
{
    public function ancestor()
    {
        $property = $this->ancestorProperty;

        return $this->$property;
    }

    public function associateAncestor(Model $instance)
    {
        $this->{$this->ancestorProperty}
        = $instance;
    }
}
