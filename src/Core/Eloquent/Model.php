<?php
namespace Mopsis\Core\Eloquent;

use DomainException;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mopsis\Contracts\Hierarchical;
use Mopsis\Contracts\Model as ModelInterface;
use Mopsis\Core\App;
use Mopsis\Core\Cache;
use Mopsis\Extensions\Stringifier;
use Mopsis\Security\Token;

abstract class Model extends EloquentModel implements ModelInterface
{
    const CREATED_BY = 'created_by';

    const DELETED_AT = 'deleted_at';

    const UPDATED_BY = 'updated_by';

    protected static $datatypes;

    protected $appends = [];

    protected $guarded = ['id'];

    protected $orderBy;

    protected $stringifier;

    // @Override
    public function __construct(array $attributes = [])
    {
        if (!isset($this->table)) {
            $this->table = preg_replace('/_models$/', '', $this->getTable());
        }

        if ($this->guarded !== ['*']) {
            $this->guarded = array_merge($this->guarded, [
                'slug',
                static::CREATED_AT,
                static::UPDATED_AT,
                static::DELETED_AT
            ]);
        }

        foreach ($this->getDataTypes() as $attribute => $type) {
            $this->fillable[] = $attribute;

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

    // @Override
    public function __isset($key)
    {
        return parent::__isset($key) ?: parent::__isset(snake_case($key));
    }

    // @Override
    public function __toString()
    {
        return class_basename($this) . ':' . ($this->id ?: 0);
    }

    // @Override
    public static function boot()
    {
        parent::boot();
        static::observe(new ModelObserver());

        try {
            if ($calledClass = App::identify(get_called_class())) {
                $observer = App::build('Observer', implode('\\', $calledClass));
                static::observe(new $observer());
            }
        } catch (DomainException $e) {
        }
    }

    public function clearCachedAttribute($attribute)
    {
        Cache::delete([
            $this,
            $attribute
        ]);

        return $this;
    }

    public function clearCachedAttributeRecursive($attribute)
    {
        $this->clearCachedAttribute($attribute);

        if ($this instanceof Hierarchical) {
            $this->ancestor->clearCachedAttributeRecursive($attribute);
        }

        return $this;
    }

    // @Override
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
        return Cache::get([
            $this->getTable(),
            '@dataTypes'
        ], function () {
            $columns = [];

            foreach ($this->getConnection()->select('SHOW COLUMNS FROM ' . $this->getTable()) as $column) {
                if (!$this->isFillable($column->Field)) {
                    continue;
                }

                switch (true) {
                    case preg_match('/^varchar\(\d+\)$/', $column->Type):
                        $columns[$column->Field] = 'string';
                        break;
                    case preg_match('/^int\(10\)( unsigned)?$/', $column->Type):
                        $columns[$column->Field] = 'integer';
                        break;
                    case preg_match('/^timestamp|date(time)?$/', $column->Type):
                        $columns[$column->Field] = 'datetime';
                        break;
                    case preg_match('/^tinyint\(1\)$/', $column->Type):
                        $columns[$column->Field] = 'boolean';
                        break;
                    case preg_match('/^float( unsigned)?$/', $column->Type):
                    case preg_match('/^decimal\(\d+,\d+\)( unsigned)?$/', $column->Type):
                        $columns[$column->Field] = 'float';
                        break;
                    default:
                        $columns[$column->Field] = null;
                        break;
                }
            }

            return $columns;
        });
    }

    // @Override
    public function getDates()
    {
        return array_merge($this->dates, [
            static::CREATED_AT,
            static::UPDATED_AT,
            static::DELETED_AT
        ]);
    }

    public function getFillableAttributes()
    {
        if ($this->guarded == ['*']) {
            return [];
        }

        return array_values(array_diff(array_keys($this->getDataTypes()), $this->guarded));
    }

    // @Override
    public function getForeignKey()
    {
        return isset($this->table) ? str_singular($this->table) . '_id' : parent::getForeignKey();
    }

    public function getHashAttribute()
    {
        return new Token($this);
    }

    public function getTokenAttribute()
    {
        return new Token($this, session_id());
    }

    public function getTokenStringAttribute()
    {
        return (string) $this->token;
    }

    public function hasAttribute($key)
    {
        return (array_key_exists($key, $this->attributes) || array_key_exists(snake_case($key), $this->attributes) || array_key_exists($key, $this->relations) || $this->hasGetMutator($key));
    }

    // @Override
    public function newCollection(array $models = [])
    {
        try {
            if ($calledClass = App::identify($this)) {
                $collection = App::build('Collection', implode('\\', $calledClass));

                return new $collection($models);
            }
        } catch (DomainException $e) {
        }

        return new Collection($models);
    }

    // @Override
    public function newQuery()
    {
        $builder = parent::newQuery();

        if (strlen($this->orderBy)) {
            $builder->orderByRaw($this->orderBy);
        }

        return $builder;
    }

    public function set($key, $value)
    {
        $this->$key = $value;

        return $this;
    }

    public function stringify()
    {
        return $this->stringifier ?: $this->stringifier = new Stringifier($this);
    }

    // @Override
    public function toArray()
    {
        $attributes = [];

        foreach (parent::toArray() as $key => $value) {
            $attributes[camel_case($key)] = $value;
        }

        foreach ($this->appends as $key) {
            $attributes[camel_case($key)] = $this->getAttribute($key);
        }

        return $attributes;
    }

    public function toFormData()
    {
        return $this->stringify()->toArray();
    }

    /**
     * @param  string                        $token
     * @return \Mopsis\Core\Eloquent\Model
     */
    public static function unpack($token)
    {
        $instance = Token::extract($token);

        if (is_a($instance, get_called_class())) {
            return $instance;
        }

        throw new ModelNotFoundException('Token "' . $token . '" is invalid or outdated.');
    }

    // @Override
    protected function castAttribute($key, $value)
    {
        switch ($this->getCastType($key)) {
            case 'array':
                return $this->fromJson($value) ?: [];
            case 'json':
                return App::make('Json', ['body' => $value]);
            case 'xml':
                return App::make('Xml', ['xmlData' => $value]);
            default:
                return parent::castAttribute($key, $value);
        }
    }

    protected function getCachedAttribute($attribute, callable $callback, $ttl = null)
    {
        return Cache::get([
            $this,
            $attribute
        ], $callback, $ttl);
    }
}
