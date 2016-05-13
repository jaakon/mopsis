<?php
namespace Mopsis\Contracts;

interface Traceable
{
    public function getEvents($depth = 0);

    public function setCreatedBy($value);

    public function setCreatingUser();

    public function setUpdatedBy($value);

    public function setUpdatingUser();
}
