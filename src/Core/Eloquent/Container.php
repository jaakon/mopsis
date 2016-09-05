<?php
namespace Mopsis\Core\Eloquent;

use InvalidArgumentException;
use Mopsis\Extensions\Stringifier;

abstract class Container
{
    protected $properties;

    protected $stringifier;

    public function __construct(array $properties = [])
    {
        $this->properties = $properties;
    }

    public function __get($key)
    {
        if ($this->hasGetMutator($key)) {
            return $this->{$this->getGetMutator($key)}
            ();
        }

        if (array_key_exists($key, $this->properties)) {
            return $this->properties[$key];
        }

        throw new InvalidArgumentException('property "' . $key . '" is not defined.');
    }

    public function __isset($key)
    {
        return isset($this->properties[$key]) || $this->hasGetMutator($key);
    }

    public function __set($key, $value)
    {
        if ($this->hasSetMutator($key)) {
            return $this->{$this->getSetMutator($key)}
            ($value);
        }

        if (array_key_exists($key, $this->properties)) {
            return $this->properties[$key] = $value;
        }

        throw new InvalidArgumentException('property "' . $key . '" is not defined.');
    }

    public function stringify()
    {
        return $this->stringifier ?: $this->stringifier = new Stringifier($this);
    }

    protected function getGetMutator($key)
    {
        return 'get' . studly_case($key) . 'Attribute';
    }

    protected function getSetMutator($key)
    {
        return 'set' . studly_case($key) . 'Attribute';
    }

    protected function hasGetMutator($key)
    {
        return method_exists($this, $this->getGetMutator($key));
    }

    protected function hasSetMutator($key)
    {
        return method_exists($this, $this->getSetMutator($key));
    }
}
