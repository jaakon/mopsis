<?php
namespace Mopsis\Core\FluentDao;

use Mopsis\Contracts\User;
use Mopsis\Extensions\Stringifier;

class Collection extends \Illuminate\Support\Collection
{
    protected $privilege;

    protected $stringifier;

    public function __call($method, $args)
    {
        try {
            foreach ($this->items as $item) {
                $item->$method(...$args);
            }
        } catch (\Exception $e) {
            throw new \Exception('unknown method "' . $method . '"');
        }
    }

    public function __get($key)
    {
        return $this->hasGetMutator($key) ? $this->mutateAttribute($key) : null;
    }

    public function __isset($key)
    {
        return $this->hasGetMutator($key);
    }

    public function accessibleFor(User $user, $privilege = null)
    {
        return $this->filter(function ($item) use ($user, $privilege) {
            return $user->may($privilege ?: $this->privilege, $item);
        });
    }

    public function filterBySql($query, $values = [], $orderBy = null)
    {
        if (!count($this->items)) {
            return $this;
        }

        list($query, $values) = Sql::expandQuery($query, $values);

        $dataById = [];
        $data     = new static();

        foreach ($this->items as $item) {
            $dataById[$item->id] = $item;
        }

        foreach ($this->first()->getCol('id', '(id IN (' . implode(',', array_keys($dataById)) . ')) AND (' . $query . ')', $values, $orderBy) as $id) {
            $data[] = $dataById[$id];
        }

        return $data;
    }

    public function getLengthAttribute()
    {
        return $this->count();
    }

    public static function load(array $ids)
    {
        $collection = new static();
        $model      = static::getModelClass();

        foreach ($ids as $id) {
            try {
                $collection[] = ModelFactory::load($model, $id);
            } catch (\LengthException $e) {
            }
        }

        return $collection;
    }

    public static function loadRawData(array $data)
    {
        $collection = new static();
        $model      = static::getModelClass();

        foreach ($data as $entry) {
            ModelFactory::put($collection[] = (new $model())->inject($entry));
        }

        return $collection;
    }

    public function set($key, $value)
    {
        foreach ($this->items as $item) {
            $item->{$key}
            = $value;
        }
    }

    public function sortBySql($orderBy)
    {
        return count($this->items) > 1 ? $this->filterBySql('1=1', [], $orderBy) : $this;
    }

    public function stringify()
    {
        return $this->stringifier ?: $this->stringifier = new Stringifier($this);
    }

    protected function getModelClass()
    {
        return str_replace('Collection', 'Model', get_called_class());
    }

    protected function hasGetMutator($key)
    {
        return method_exists($this, 'get' . studly_case($key) . 'Attribute');
    }

    protected function mutateAttribute($key)
    {
        return $this->{'get' . studly_case($key) . 'Attribute'}

        ();
    }
}
