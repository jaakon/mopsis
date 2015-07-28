<?php namespace Mopsis\ADR\Domain;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Builder;

abstract class AbstractRepository
{
	protected $baseQuery;
	protected $query;
	protected $relation;

	public function __call($method, $parameters)
	{
		if (method_exists($this, $scope = 'scope'.ucfirst($method))) {
			return $this->callScope($scope, $parameters);
		}

		$result = $this->query->$method(...$parameters);

		if ($result instanceof Builder) {
			return $this;
		}

		$this->newQuery();
		return $result;
	}

	public function __clone()
	{
		$this->baseQuery = clone $this->baseQuery;
		$this->query     = clone $this->baseQuery;
	}

	public function __construct(Relation $relation)
	{
		$this->relation  = $relation;
		$this->baseQuery = clone $relation->getQuery();
		$this->query     = clone $this->baseQuery;
	}

	protected function callScope($scope, $parameters)
	{
		$that = clone $this;
		$that->$scope($that->baseQuery, ...$parameters);
		$that->query = clone $that->baseQuery;

		return $that;
	}

	protected function newQuery()
	{
		$this->query = clone $this->baseQuery;
		return $this;
	}
}
