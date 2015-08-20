<?php namespace Mopsis\Components\Domain;

class PayloadFactory
{
	public function created(array $payload = [])
	{
		return new Payload\Created($payload);
	}

	public function deleted(array $payload = [])
	{
		return new Payload\Deleted($payload);
	}

	public function error(array $payload = [])
	{
		return new Payload\Error($payload);
	}

	public function found(array $payload = [])
	{
		return new Payload\Found($payload);
	}

	public function newEntity(array $payload = [])
	{
		return new Payload\NewEntity($payload);
	}

	public function notCreated(array $payload = [])
	{
		return new Payload\NotCreated($payload);
	}

	public function notDeleted(array $payload = [])
	{
		return new Payload\NotDeleted($payload);
	}

	public function notFound(array $payload = [])
	{
		return new Payload\NotFound($payload);
	}

	public function notUpdated(array $payload = [])
	{
		return new Payload\NotUpdated($payload);
	}

	public function notValid(array $payload = [])
	{
		return new Payload\NotValid($payload);
	}

	public function updated(array $payload = [])
	{
		return new Payload\Updated($payload);
	}

	public function valid(array $payload = [])
	{
		return new Payload\Valid($payload);
	}
}
