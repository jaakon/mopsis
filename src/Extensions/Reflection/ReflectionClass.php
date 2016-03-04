<?php
namespace Mopsis\Extensions\Reflection;

class ReflectionClass extends \ReflectionClass
{
    // @Override
    public function getConstructor()
    {
        return $this->newMethod(parent::getConstructor());
    }

    // @Override

    public function getMethod($name)
    {
        return $this->newMethod(parent::getMethod($name));
    }

    public function getMethods($filter = null)
    {
        return array_map(function ($method) {
            return $this->newMethod($method);
        }, parent::getMethods($filter));
    }

    // @Override

    protected function newMethod(\ReflectionMethod $method)
    {
        return new ReflectionMethod($method->class, $method->name);
    }
}
