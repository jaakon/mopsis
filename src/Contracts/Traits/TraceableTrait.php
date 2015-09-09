<?php namespace Mopsis\Contracts\Traits;

use Mopsis\Core\Auth;

trait TraceableTrait
{
	public function getEvents($depth = 0)
	{
		if ($depth > 0) {
			throw new \BadMethodCallException("not implemented");
		}

		return $this->events;
	}

	/** @Override */
	public function setCreatedAt($value)
	{
		parent::setCreatedAt($value);
		$this->setCreatingUser();
	}

	public function setCreatingUser()
	{
		$this->setCreatedBy(Auth::user()->getKey());
	}

	public function setCreatedBy($value)
	{
		$this->{static::CREATED_BY} = $value;
	}

	/** @Override */
	public function setUpdatedAt($value)
	{
		parent::setUpdatedAt($value);
		$this->setUpdatingUser();
	}

	public function setUpdatingUser()
	{
		$this->setUpdatedBy(Auth::user()->getKey());
	}

	public function setUpdatedBy($value)
	{
		$this->{static::UPDATED_BY} = $value;
	}
}
