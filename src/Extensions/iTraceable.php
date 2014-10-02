<?php namespace Mopsis\Extensions;

interface iTraceable
{
	public function setCreatingUser();
	public function setUpdatingUser();
	public function setCreatedBy($value);
	public function setUpdatedBy($value);
}

trait iTraceableTrait
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
		$this->setCreatedBy(\Mopsis\Auth::user()->getKey());
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
		$this->setUpdatedBy(\Mopsis\Auth::user()->getKey());
	}
}