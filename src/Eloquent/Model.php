<?php
namespace Mopsis\Eloquent;

abstract class Model extends \Illuminate\Database\Eloquent\Model
{
	const DELETED_AT = 'deleted_at';
	const CREATED_BY = 'created_by';
	const UPDATED_BY = 'updated_by';

	protected $guarded = ['id'];
	protected $orderBy;

	public static function boot()
	{
		parent::boot();
		static::observe(new ModelObserver);

		if (class_exists($observer = get_called_class().'Observer')) {
			static::observe(new $observer);
		}
	}

	public static function unpack($token)
	{
		$model = \Mopsis\Types\Token::extract($token);

		if (is_a($model, get_called_class())) {
			return $model;
		}

		throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Token "'.$token.'" is invalid or outdated.');
	}

	public function __construct(array $attributes = [])
	{
		if ($this->guarded !== ['*']) {
			$this->guarded = array_merge($this->guarded, ['slug', static::CREATED_AT, static::UPDATED_AT, static::DELETED_AT]);
		}

		parent::__construct($attributes);
	}

	public function __isset($key)
	{
		return parent::__isset($key) ?: parent::__isset(snake_case($key));
	}

	public function __toString()
	{
		return getClassName($this).':'.($this->id ?: 0);
	}

	public function clearCachedAttribute($attribute, $ascending = false)
	{
		\App::make('logger')->info('Clearing cache for '.get_class($this).':'.$this->id.'->'.$attribute);
		\App::make('cache')->getItem($this, $attribute)->clear();

		if ($ascending && $this instanceof \Mopsis\Extensions\iHierarchical) {
			$this->ancestor->clearCachedAttribute($attribute, true);
		}

		return $this;
	}

	public function getAttribute($key)
	{
		if (ctype_lower($key) || is_int(strpos($key, '_'))) {
			return parent::getAttribute($key);
		}

		$snakeCaseKey = snake_case($key);
		$inAttributes = array_key_exists($snakeCaseKey, $this->attributes);

		return parent::getAttribute($inAttributes ? $snakeCaseKey : $key);
	}

	public function getColumns()
	{
		$columns = [];

		foreach ($this->getConnection()->select('SHOW COLUMNS FROM '.$this->getTable()) as $entry) {
			if ($this->isFillable($entry['Field'])) {
				$columns[] = $entry['Field'];
			}
		}

		return $columns;
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

	public function isBound()
	{
		return $this->exists;
	}

	public function newCollection(array $models = [])
	{
		return new Collection($models);
	}

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

	protected function getCachedAttribute($attribute, \Closure $callback, $ttl = null)
	{
		$item  = \App::make('cache')->getItem($this, $attribute);
		$value = $item->get(\Stash\Invalidation::OLD);

		if ($item->isMiss()) {
			$item->lock();
			\App::make('logger')->info('Building cache for '.get_class($this).':'.$this->id.'->'.$attribute);
			$value = $callback();
			$item->set($value, $ttl);
		}

		return $value;
	}
}
