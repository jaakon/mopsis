<?php namespace Mopsis\Reflection;

class ReflectionClass extends \ReflectionClass
{
	public function getConstructor()
	{
		return $this->newMethod(parent::getConstructor());
	}

	protected function newMethod(\ReflectionMethod $method)
	{
		return new ReflectionMethod($method->class, $method->name);
	}

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
}
