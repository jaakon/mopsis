<?php
namespace Mopsis\Components;

class PayloadFactory
{
    private $aliases = [];

    public function accepted(array $data = [])
    {
        return (new Payload\Accepted($data))->setAliases($this->aliases);
    }

    public function created(array $data = [])
    {
        return (new Payload\Created($data))->setAliases($this->aliases);
    }

    public function deleted(array $data = [])
    {
        return (new Payload\Deleted($data))->setAliases($this->aliases);
    }

    public function error(array $data = [])
    {
        return (new Payload\Error($data))->setAliases($this->aliases);
    }

    public function found(array $data = [])
    {
        return (new Payload\Found($data))->setAliases($this->aliases);
    }

    public function gone(array $data = [])
    {
        return (new Payload\Gone($data))->setAliases($this->aliases);
    }

    public function notCreated(array $data = [])
    {
        return (new Payload\NotCreated($data))->setAliases($this->aliases);
    }

    public function notDeleted(array $data = [])
    {
        return (new Payload\NotDeleted($data))->setAliases($this->aliases);
    }

    public function notFound(array $data = [])
    {
        return (new Payload\NotFound($data))->setAliases($this->aliases);
    }

    public function notUpdated(array $data = [])
    {
        return (new Payload\NotUpdated($data))->setAliases($this->aliases);
    }

    public function notValid(array $data = [])
    {
        return (new Payload\NotValid($data))->setAliases($this->aliases);
    }

    public function setAlias($link, $target)
    {
        if ($link === $target) {
            return;
        }

        $this->aliases[$link] = $target;

        foreach ($this->aliases as $oldLink => $oldTarget) {
            if ($oldTarget === $link) {
                $this->aliases[$oldLink] = $target;
            }
        }
    }

    public function unauthorized(array $data = [])
    {
        return (new Payload\Unauthorized($data))->setAliases($this->aliases);
    }

    public function updated(array $data = [])
    {
        return (new Payload\Updated($data))->setAliases($this->aliases);
    }
}
