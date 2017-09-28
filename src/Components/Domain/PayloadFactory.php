<?php
namespace Mopsis\Components\Domain;

/*
ACCEPTED:          A command has been accepted for later processing.
AUTHENTICATED:     An authentication attempt succeeded.
AUTHORIZED:        An authorization request succeeded.
FAILURE:           There was a generic failure of some sort.
NOT_ACCEPTED:      A command failed to be accepted.
NOT_AUTHENTICATED: The user is not authenticated.
NOT_AUTHORIZED:    The user is not authorized for the action.
PROCESSING:        A command is in-process but not finished.
SUCCESS:           There was a generic success of some sort.
 */

class PayloadFactory
{
    public function created(array $data = [])
    {
        return new Payload\Created($data);
    }

    public function deleted(array $data = [])
    {
        return new Payload\Deleted($data);
    }

    public function error(array $data = [])
    {
        return new Payload\Error($data);
    }

    public function found(array $data = [])
    {
        return new Payload\Found($data);
    }

    public function newEntity(array $data = [])
    {
        return new Payload\NewEntity($data);
    }

    public function notCreated(array $data = [])
    {
        return new Payload\NotCreated($data);
    }

    public function notDeleted(array $data = [])
    {
        return new Payload\NotDeleted($data);
    }

    public function notFound(array $data = [])
    {
        return new Payload\NotFound($data);
    }

    public function notUpdated(array $data = [])
    {
        return new Payload\NotUpdated($data);
    }

    public function notValid(array $data = [])
    {
        return new Payload\NotValid($data);
    }

    public function seeOther(array $data = [])
    {
        return new Payload\SeeOther($data);
    }

    public function updated(array $data = [])
    {
        return new Payload\Updated($data);
    }

    public function valid(array $data = [])
    {
        return new Payload\Valid($data);
    }
}
