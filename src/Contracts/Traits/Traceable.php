<?php
namespace Mopsis\Contracts\Traits;

trait Traceable
{
    public function getEvents($depth = 0)
    {
        if ($depth > 0) {
            throw new \BadMethodCallException('not implemented');
        }

        return $this->events;
    }

    /**
     * @Override
     */
    public function setCreatedAt($value)
    {
        parent::setCreatedAt($value);
        $this->setCreatingUser();
    }

    public function setCreatedBy($value)
    {
        $this->{static::CREATED_BY}
        = $value;
    }

    public function setCreatingUser()
    {
        $this->setCreatedBy(\Mopsis\Core\Auth::user()->getKey());
    }

    /**
     * @Override
     */
    public function setUpdatedAt($value)
    {
        parent::setUpdatedAt($value);
        $this->setUpdatingUser();
    }

    public function setUpdatedBy($value)
    {
        $this->{static::UPDATED_BY}
        = $value;
    }

    public function setUpdatingUser()
    {
        $this->setUpdatedBy(\Mopsis\Core\Auth::user()->getKey());
    }
}
