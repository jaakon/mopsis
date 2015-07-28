<?php namespace Mopsis\ADR\Domain;

use Mopsis\Eloquent\Model;

abstract class AbstractGateway
{
	protected $model;

	public function fetchAll()
	{
		return $this->model->all();
	}

	public function fetchById($id)
	{
		return $this->model->find($id);
	}

	public function fetchByToken($token)
	{
		return $this->model->unpack($token);
	}

	public function find($sql, array $bindings)
	{
		return $this->model->whereRaw($sql, $bindings);
	}

	public function newEntity(array $attributes = [])
	{
		return $this->model->newInstance($attributes);
	}

	public function findOne($sql, array $bindings = [])
	{
		return $this->find(...$this->expandQuery($sql, $bindings))->first();
	}

	public function findMany($sql, array $bindings = [])
	{
		return $this->find(...$this->expandQuery($sql, $bindings))->get();
	}

	public function create(Model $instance, $data)
	{
		return $instance->fill($this->getAcceptedData($instance, $data))->save() ? $instance : false;
	}

	public function update(Model $instance, $data)
	{
		return $instance->update($this->getAcceptedData($instance, $data)) ? $instance : false;
	}

	public function delete(Model $instance)
	{
		return true; //$instance->delete();
	}

	public function set(Model $instance, $key, $value)
	{
		$instance->setAttribute($key, $value);
		$instance->save();

		return $instance;
	}

	private function getAcceptedData(Model $instance, $data)
	{
		foreach ($data as $key => $value) {
			unset($data[$key]);
			$data[snake_case($key)] = $value;
		}

		return array_intersect_key($data, array_flip($instance->getFillableAttributes()));
	}

	private function expandQuery($sql, array $bindings)
	{
		if (!is_array($sql)) {
			return [$sql, $bindings];
		}

		return ['`'.implode('`=? AND `', array_keys($sql)).'`=?', array_values($sql)];
	}
}
