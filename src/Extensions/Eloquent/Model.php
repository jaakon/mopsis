<?php namespace Mopsis\Extensions\Eloquent;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mopsis\Contracts\Hierarchical;
use Mopsis\Core\Cache;
use Mopsis\Extensions\Stringifier;
use Mopsis\Reflection\ReflectionClass;

abstract class Model extends \Illuminate\Database\Eloquent\Model
{
	const CREATED_BY = 'created_by';
	const UPDATED_BY = 'updated_by';
	const DELETED_AT = 'deleted_at';

	protected $guarded   = ['id'];
	protected $sluggable = ['on_update' => true];
	protected $orderBy;
	protected $stringifier;

	/** @Override */
	public static function boot()
	{
		parent::boot();
		static::observe(new ModelObserver);

		if (class_exists($observer = get_called_class() . 'Observer')) {
			static::observe(new $observer);
		}
	}

	/**
	 * @param string $token
	 *
	 * @return \Mopsis\Extensions\Eloquent\Model
	 */
	public static function unpack($token)
	{
		$instance = \Mopsis\Types\Token::extract($token);
		$class    = get_called_class();

		if ($instance instanceof $class) {
			return $instance;
		}

		throw new ModelNotFoundException('Token "' . $token . '" is invalid or outdated.');
	}

	/** @Override */
	public function __construct(array $attributes = [])
	{
		if ($this->guarded !== ['*']) {
			$this->guarded = array_merge($this->guarded, [
				'slug',
				static::CREATED_AT,
				static::UPDATED_AT,
				static::DELETED_AT
			]);
		}

		foreach ($this->getDataTypes() as $attribute => $type) {
			if ($type === null) {
				continue;
			}

			if ($type === 'datetime') {
				$this->dates[] = $attribute;
				continue;
			}

			if (!isset($this->casts[$attribute])) {
				$this->casts[$attribute] = $type;
			}
		}

		parent::__construct($attributes);
	}

	/** @Override */
	public function __isset($key)
	{
		return parent::__isset($key) ?: parent::__isset(snake_case($key));
	}

	public function __toString()
	{
		return substr((new \ReflectionClass($this))->getShortName(), 0, -5) . ':' . ($this->id ?: 0);
	}

	public function clearCachedAttribute($attribute)
	{
		Cache::clear([$this, $attribute]);

		return $this;
	}

	public function getUriRecursive()
	{
		if ($this->exists() && isset($this->uri)) {
			return $this->uri;
		}

		if ($this instanceof iHierarchical) {
			return $this->ancestor->getUriRecursive();
		}

		return;
	}

	/** @Override */
	public function hasMany($related, $foreignKey = null, $localKey = null)
	{
		list($module, $domain) = explode('\\', $related);

		return \Mopsis\Core\App::make('App\\' . $module . '\Domain\\' . $domain . 'Gateway')
			 ->newRepository(parent::hasMany('App\\' . $module . '\Domain\\' . $domain . 'Model', $foreignKey, $localKey));
	}

	public function clearCachedAttributeRecursive($attribute)
	{
		$this->clearCachedAttribute($attribute);

		if ($this instanceof Hierarchical) {
			$this->ancestor->clearCachedAttributeRecursive($attribute);
		}

		return $this;
	}

	public function findRelations(Model $model)
	{
		$class = new ReflectionClass($this);
		$className = $class->getName();
		$modelName = get_class($model);

		return array_map(
			function ($method) {
				return $method->name;
			},
			array_filter(
				$class->getMethods(\ReflectionMethod::IS_PUBLIC),
				function ($method) use ($className, $modelName) {
					return $method->class === $className
					&& !preg_match('/^[gs]et\w+Attribute$/', $method->name)
					&& strpos($method->getBody(), $modelName) !== false;
				}
			)
		);
	}

	/** @Override */
	public function getAttribute($key)
	{
		if (ctype_lower($key) || is_int(strpos($key, '_'))) {
			return parent::getAttribute($key);
		}

		$snakeCaseKey = snake_case($key);
		$inAttributes = array_key_exists($snakeCaseKey, $this->attributes);

		return parent::getAttribute($inAttributes ? $snakeCaseKey : $key);
	}

	public function getDataTypes()
	{
		return Cache::get([get_called_class(), '@dataTypes'], function () {
			$columns = [];

			foreach ($this->getConnection()->select('SHOW COLUMNS FROM ' . $this->getTable()) as $column) {
				if (!$this->isFillable($column->Field)) {
					continue;
				}

				switch (true) {
					case preg_match('/^tinyint\(1\)$/', $column->Type):
						$columns[$column->Field] = 'boolean';
						continue;
					case preg_match('/^int\(10\)( unsigned)?$/', $column->Type):
						$columns[$column->Field] = 'integer';
						continue;
					case preg_match('/^float( unsigned)?$/', $column->Type):
					case preg_match('/^decimal\(\d+,\d+\)( unsigned)?$/', $column->Type):
						$columns[$column->Field] = 'float';
						continue;
					case preg_match('/^timestamp|date(time)?$/', $column->Type):
						$columns[$column->Field] = 'datetime';
						continue;
					default:
						$columns[$column->Field] = null;
						continue;
				}
			}

			return $columns;
		});
	}

	/** @Override */
	public function getDates()
	{
		$defaults = [static::CREATED_AT, static::UPDATED_AT, static::DELETED_AT];

		return array_merge($this->dates, $defaults);
	}

	public function getFillableAttributes()
	{
		if ($this->guarded == ['*']) {
			return [];
		}

		return array_values(array_diff(array_keys($this->getDataTypes()), $this->guarded));
	}

	/** @Override */
	public function getForeignKey()
	{
		return isset($this->table) ? str_singular($this->table) . '_id' : parent::getForeignKey();
	}

	public function getHashAttribute()
	{
		return new \Mopsis\Types\Token($this);
	}

	public function getTokenAttribute()
	{
		return new \Mopsis\Types\Token($this, session_id());
	}

	public function getTokenStringAttribute()
	{
		return (string)$this->token;
	}

	/** @Override */
	public function newCollection(array $models = [])
	{
		if (class_exists($collection = str_replace('Model', 'Collection', get_called_class()))) {
			return new $collection($models);
		}

		return new Collection($models);
	}

	/** @Override */
	public function newQuery($excludeDeleted = true)
	{
		$builder = parent::newQuery($excludeDeleted);

		if (strlen($this->orderBy)) {
			$builder->orderByRaw($this->orderBy);
		}

		return $builder;
	}

	public function set($key, $value)
	{
		$this->{$key} = $value;

		return $this;
	}

	public function stringify()
	{
		return $this->stringifier ?: $this->stringifier = new Stringifier($this);
	}

	/** @Override */
	public function toArray()
	{
		$attributes = [];

		foreach (parent::toArray() as $key => $value) {
			$attributes[camel_case($key)] = $value;
		}

		return $attributes;
	}

	protected function getCachedAttribute($attribute, callable $callback, $ttl = null)
	{
		return Cache::get([$this, $attribute], $callback, $ttl);
	}
}
