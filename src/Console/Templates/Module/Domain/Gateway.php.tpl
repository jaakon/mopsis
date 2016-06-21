<?php namespace App\{{MODULE}};

use App\{{MODULE}}\{{DOMAIN}}Model as Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Mopsis\Components\Domain\AbstractGateway;

class {{DOMAIN}}Gateway extends AbstractGateway
{
	public function __construct(Model $model)
	{
		$this->model = $model;
	}

	public function newRepository(Relation $relation)
	{
		return new {{DOMAIN}}Repository($relation);
	}
}
