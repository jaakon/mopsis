<?php namespace Mopsis\Extensions\Twig;

use Illuminate\Database\Eloquent\Relations\Relation;
use Mopsis\Extensions\Eloquent\Model;

abstract class Template extends \Twig_Template
{
	protected function getAttribute($object, $item, array $arguments = [], $type = self::ANY_CALL, $isDefinedTest = false, $ignoreStrictCheck = false)
	{
		if ($type !== self::METHOD_CALL && $object instanceof Model) {
			if ($isDefinedTest) {
				return $item === 'exists' || $object->hasAttribute($item);
			}

			return $item === 'exists' ? $object->exists : $object->getAttribute($item);
		}

		if ($type !== self::METHOD_CALL && $object instanceof Relation) {
//			$result = $object->$item;
//			debug($object, $item, $result, $object->get());
//			die();
		}

		return parent::getAttribute($object, $item, $arguments, $type, $isDefinedTest, $ignoreStrictCheck);
	}
}
