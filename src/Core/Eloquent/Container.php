<?php
namespace Mopsis\Core\Eloquent;

use Mopsis\Extensions\Stringifier;

class Container
{
    protected $stringifier;

    public function __get($key)
    {
        return $this->hasGetMutator($key) ? $this->{'get' . studly_case($key) . 'Attribute'}() : null;
    }

    public function __isset($key)
    {
        return $this->hasGetMutator($key);
    }

    public function __set($key, $value)
    {
        return $this->hasSetMutator($key) ? $this->{'set' . studly_case($key) . 'Attribute'}($value) : null;
    }

    public function stringify()
    {
        return $this->stringifier ?: $this->stringifier = new Stringifier($this);
    }

    protected function hasGetMutator($key)
    {
        return method_exists($this, 'get' . studly_case($key) . 'Attribute');
    }

    protected function hasSetMutator($key)
    {
        return method_exists($this, 'set' . studly_case($key) . 'Attribute');
    }
}