<?php namespace Mopsis\Components\Domain;

use Mopsis\Components\Model\Model;

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
		return $instance->fill($this->getAcceptedData($instance, $this->getRestructuredData($data)))->save() ? $instance : false;
	}

	public function update(Model $instance, $data)
	{
		return $instance->update($this->getAcceptedData($instance, $this->getRestructuredData($data))) ? $instance : false;
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
				die('CARCRASH!!');
			}

			if (!is_array($data[$match[1]])) {
				$data[$match[1]] = [];
			}

			$data[$match[1]][$match[2]] = $value;
		}

		return $data;
	}

	protected function expandQuery($sql, array $bindings)
	{
		if (!is_array($sql)) {
			return [$sql, $bindings];
		}

		return ['`'.implode('`=? AND `', array_keys($sql)).'`=?', array_values($sql)];
	}
}
