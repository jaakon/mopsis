<?php namespace Mopsis\Extensions\FluentDao;

use Mopsis\Contracts\Model;

class TypeFactory
{
	public static function cast($value, $type)
	{
		if ($value === null && $type !== 'json') {
			return $value;
		}

		switch ($type) {
			case 'string':
				return (string)$value;
			case 'integer':
				return (int)$value;
			case 'boolean':
				return (bool)$value;
			case 'float':
				return (float)$value;
			case 'decimal':
				return str_replace(',', '.', $value);
			case 'date':
			case 'datetime':
				return $value !== '' ? self::create('\Mopsis\Types\DateTime', $value ?: 'now') : null;
			case 'enum':
				return (string)$value;
			case 'json':
				return self::create('\Mopsis\Types\JSON', $value);
			case 'model':
				if ($value instanceof Model) {
					return $value;
				}

				if (preg_match('/^([a-z]+):(\d+)$/i', $value, $m)) {
					try {
						return ModelFactory::load($m[1], $m[2]);
					} catch (\LengthException $e) {
						throw new \Exception('reference "' . $value . '" is invalid');
					}
				}
		}

		return $value;
	}

	public static function create($type, $value)
	{
		return $value instanceof $type ? $value : new $type($value);
	}
}
