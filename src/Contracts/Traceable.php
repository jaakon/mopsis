<?php namespace Mopsis\Contracts;

interface Traceable
{
	public function getEvents($depth = 0);
}

trait TraceableTrait
{
	public function getEvents($depth = 0)
	{
		if ($depth > 0)
			throw new \BadMethodCallException("not implemented");

		return $this->events;
	}
}

/*
trait TraceableTrait
{
	public function setCreatedAt($value)
	{
		parent::setCreatedAt($value);
		$this->setCreatingUser();
	}

	public function setCreatedBy($value)
	{
		$this->{static::CREATED_BY} = $value;
	}

	public function setCreatingUser()
	{
		$this->setCreatedBy(\Mopsis\Core\Auth::user()->getKey());
	}

	public function setUpdatedAt($value)
	{
		parent::setUpdatedAt($value);
		$this->setUpdatingUser();
	}

	public function setUpdatedBy($value)
	{
		$this->{static::UPDATED_BY} = $value;
	}

	public function setUpdatingUser()
	{
		$this->setUpdatedBy(\Mopsis\Core\Auth::user()->getKey());
	}
}

interface TraceableInterface
{
	public function setCreatingUser();

	public function setUpdatingUser();

	public function setCreatedBy($value);

	public function setUpdatedBy($value);
}
*/
