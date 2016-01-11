<?php namespace Mopsis\Components\Domain;

use Illuminate\Database\Query\Builder;
use Mopsis\Extensions\Eloquent\Model;

/**
 * @property Model $model
 */
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

	public function newEntity(array $attributes = []) : Model
	{
		return $this->model->newInstance($attributes);
	}

	public function findOne($sql, array $bindings = [])
	{
		return $this->find(...$this->expandQuery($sql, $bindings))->first();
	}

	public function find($sql, array $bindings): Builder
	{
		return $this->model->whereRaw($sql, $bindings);
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

	public function create(Model $instance, $data)
	{
		return $instance->fill($this->getAcceptedData($instance, $this->getRestructuredData($data)))->save() ? $instance : false;
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

	public function update(Model $instance, $data)
	{
		return $instance->update($this->getAcceptedData($instance, $this->getRestructuredData($data))) ? $instance : false;
	}

	public function delete(Model $instance)
	{
		return $instance->delete();
	}

	public function set(Model $instance, $key, $value)
	{
		$instance->setAttribute($key, $value);
		$instance->save();

		return $instance;
	}
}
