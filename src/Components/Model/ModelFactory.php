<?php namespace Mopsis\Components\Model;

use Mopsis\Core\Cache;
use Mopsis\Extensions\Sql;
use Mopsis\Types\TypeFactory;

abstract class ModelFactory
{
	const NS_MODELS          = '\\App\\Models\\';
	protected static $models = [];

//=== PUBLIC STATIC FUNCTIONS ==================================================

	public static function findClass($table)
	{
		return array_search($table, self::getMapping());
	}

	public static function findTable($class)
	{
		return self::getMapping($class) ?: false;
	}

	public static function getConnections($class)
	{
		return self::readFromCache($class, 'references', function () use ($class) {
			$table	= self::findTable($class);
			$result	= [];

			// ----- OUTBOUND -----
			foreach (Sql::db()->getOutboundReferences($table, ['id']) as $attribute => $data) {
				$refClass = getClassName(ModelFactory::findClass($data['table']));
				$result[preg_replace('/Id$/', '', $attribute)] = [
					'type'      => 'outbound',
					'class'     => $refClass,
					'attribute' => $attribute,
				];
			}

			// ----- INBOUND -----
			foreach (Sql::db()->getInboundReferences($table, ['id']) as $key => $data) {
				$result[$key] = [
					'type'  => 'inbound',
					'query' => '`'.$data['reference'].'`=?',
				];
			}

			// ----- MIXED_INBOUND -----
			foreach (Sql::db()->getAll("SELECT table_name, column_name FROM information_schema.columns WHERE table_schema=DATABASE() AND column_type='text' AND column_comment LIKE 'model:%".getClassName($class)."%'") as $entry) {
				$result[$entry['table_name']] = [
					'type'  => 'mixed_inbound',
					'query' => '`'.$entry['column_name'].'`=?',
				];
			}

			// ----- CROSSBOUND -----
			foreach (Sql::db()->getCrossboundReferences($table) as $pivot => $data) {
				if (!($master = $data[$table])) {
					throw new \Exception('invalid crossbound table "'.$pivot.'"');
				}

				unset($data[$table]);

				foreach ($data as $table => $entry) {
					$result[$table] = [
						'type'       => 'crossbound',
						'pivot'      => $pivot,
						'identifier' => $entry['reference'],
						'query'      => '`'.$master['reference'].'`=?',
					];
				}
			}

			return $result;
		});
	}

	public static function getConfig($class)
	{
		if (!($table = self::findTable($class))) {
			throw new \Exception('table configuration for class "'.$class.'" is missing');
		}

		return self::readFromCache($class, 'config', function () use ($table) {
			$config = array_merge(['table' => $table], Sql::db()->getDefaults($table));

			foreach ($config['defaults'] as $key => $value) {
				$config['defaults'][$key] = TypeFactory::cast($value, $config['types'][$key]);
			}

			return $config;
		});
	}

	public static function load($class, $id, $useCache = true)
	{
		$model	= getClassName($class);
		$class	= self::NS_MODELS . $model;

		if (!is_array(self::$models[$model])) {
			self::$models[$model] = [];
		}

		if ($useCache && self::$models[$model][$id]) {
			return self::$models[$model][$id];
		}

		$object = new $class($id);

		return $object->exists ? (self::$models[$model][$id] = $object) : null;
	}

	public static function put($object)
	{
		$model = getClassName(get_class($object));

		if (!is_array(self::$models[$model])) {
			self::$models[$model] = [];
		}

		self::$models[$model][$object->id] = $object;
	}

	public static function readFromCache($class, $property, \Closure $callback, $ttl = null)
	{
		return Cache::get([str_replace('\\', '/', $class), $property], $callback, $ttl);
	}

	protected static function getMapping($class = null)
	{
		$mapping = self::readFromCache('Models/@', 'classes', function () {
			$index = [];

			foreach (Sql::db()->getAll("SELECT c.table_name, c.column_name, t.table_comment FROM information_schema.columns c LEFT JOIN information_schema.tables t ON t.table_schema=c.table_schema AND t.table_name=c.table_name WHERE c.table_schema=DATABASE() AND c.column_name='id' AND c.column_key='PRI' AND t.table_comment<>''") as $table) {
				if (!preg_match('/^(\w+)(;.*)?$/', $table['table_comment'], $m)) {
					continue;
				}

				if (empty($table['column_name'])) {
					throw new \Exception('identifier could not be set (table ['.$table['table_name'].'] has no primary key)');
				}

				$index[ltrim(self::NS_MODELS, '\\') . $m[1]] = $table['table_name'];
			}

			if (!count($index)) {
				throw new \Exception('no class definitions found in database');
			}

			return $index;
		});

		return is_null($class) ? $mapping : $mapping[$class];
	}
}
