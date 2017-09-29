<?php
namespace Mopsis\Components\Domain;

use Illuminate\Database\Eloquent\Relations\Relation;
use Mopsis\Contracts\Model;

abstract class AbstractRepository
{
    protected $model;

    public function all()
    {
        return $this->model->all();
    }

    public function create(Model $instance, $data)
    {
        return $instance->fill($this->getAcceptedData($instance, $this->getRestructuredData($data)))->save() ? $instance : false;
    }

    public function delete(Model $instance)
    {
        return $instance->delete();
    }

    public function find($sql, array $bindings)
    {
        return $this->model->whereRaw($sql, $bindings);
    }

    public function findById($id)
    {
        return $this->model->find($id);
    }

    public function findByToken($token)
    {
        return $this->model->unpack($token);
    }

    public function findMany($sql, array $bindings = [], $offset = 0, $length = null)
    {
        $query = $this->find(...$this->expandQuery($sql, $bindings));

        if ($offset > 0) {
            $query = $query->skip($offset);
        }

        if ($length > 0) {
            $query = $query->take($length);
        }

        return $query->get();
    }

    public function findOne($sql, array $bindings = [])
    {
        return $this->find(...$this->expandQuery($sql, $bindings))->first();
    }

    public function newInstance(array $attributes = []): Model
    {
        return $this->model->newInstance($attributes);
    }

    public function newRepository(Relation $relation)
    {
        return new Repository($relation);
    }

    public function set(Model $instance, $key, $value)
    {
        $instance->setAttribute($key, $value);
        $instance->save();

        return $instance;
    }

    public function update(Model $instance, $data)
    {
        return $instance->update($this->getAcceptedData($instance, $this->getRestructuredData($data))) ? $instance : false;
    }

    public function updateOrCreate(array $attributes, array $values = [])
    {
        return $this->model->updateOrCreate($attributes, $this->getAcceptedData($this->model, $values));
    }

    protected function expandQuery($sql, array $bindings)
    {
        if (!is_array($sql)) {
            return [
                $sql,
                $bindings
            ];
        }

        return [
            '`' . implode('`=? AND `', array_keys($sql)) . '`=?',
            array_values($sql)
        ];
    }

    protected function getAcceptedData(Model $instance, $data)
    {
        foreach ($data as $key => $value) {
            unset($data[$key]);
            $data[snake_case($key)] = $value;
        }

        return array_intersect_key($data, array_flip($instance->getFillableAttributes()));
    }

    protected function getRestructuredData($data)
    {
        foreach ($data as $key => $value) {
            if (!preg_match('/^(\w+)\.(\w+)$/', $key, $match)) {
                continue;
            }

            unset($data[$key]);

            if (isset($data[$match[1]]) && !is_array($data[$match[1]])) {
                exit('CARCRASH!!');
            }

            if (!is_array($data[$match[1]])) {
                $data[$match[1]] = [];
            }

            $data[$match[1]][$match[2]] = $value;
        }

        return $data;
    }
}
