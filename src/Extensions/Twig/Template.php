<?php namespace Mopsis\Extensions\Twig;

use BadMethodCallException;
use Exception;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Relation;
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

		if ($type !== self::METHOD_CALL && $object instanceof Relation) {
			if ($object instanceof BelongsTo) {
				return $object->getResults()->$item;
			}
		}

		try {
			return parent::getAttribute($object, $item, $arguments, $type, $isDefinedTest, $ignoreStrictCheck);
		} catch (BadMethodCallException $e) {
			$element = is_object($object) ? get_class($object) : gettype($object);
			throw new Exception('cannot find method or property "' . $item . '" of ' . $element);
		}
	}
}
