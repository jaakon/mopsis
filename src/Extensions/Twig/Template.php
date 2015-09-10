<?php namespace Mopsis\Extensions\Twig;

use Mopsis\Extensions\Eloquent\Model;

abstract class Template extends \Twig_Template
{
	protected function getAttribute($object, $item, array $arguments = [], $type = self::ANY_CALL, $isDefinedTest = false, $ignoreStrictCheck = false)
	{
		if ($type !== self::METHOD_CALL && $object instanceof Model) {
			if ($item === 'exists') {
				return $isDefinedTest ?: $object->exists;
			}

			if ($object->hasAttribute($item)) {
				return $isDefinedTest ?: $object->getAttribute($item);
			}
		}

		return parent::getAttribute($object, $item, $arguments, $type, $isDefinedTest, $ignoreStrictCheck);
	}
}
