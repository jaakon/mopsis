<?php
namespace Mopsis\Components\Payload;

use Mopsis\Contracts\Hierarchical;
use Mopsis\Contracts\Model;
use Mopsis\Contracts\Payload;

abstract class AbstractPayload implements Payload
{
    protected $aliases = [];

    protected $data;

    protected $redirect;

    protected $status = 501;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function add(array $data)
    {
        $this->data = array_merge($this->data, $data);

        return $this;
    }

    public function dissolve(Payload $payload)
    {
        $this->data    = array_merge($payload->get(), $this->data);
        $this->aliases = array_merge($payload->getAliases(), $this->aliases);

        return $this;
    }

    public function get($key = null)
    {
        if ($key === null) {
            return $this->toArray();
        }

        return $this->data[$key] ?: null;
    }

    public function getAliases()
    {
        return $this->aliases;
    }

    public function getName()
    {
        return lcfirst(array_pop(explode('\\', get_class($this))));
    }

    public function getRedirect()
    {
        if ($this->redirect) {
            return $this->redirect;
        }

        if (isset($this->data['#instance'])) {
            $instance = $this->data['#instance'];

            if ($instance instanceof Hierarchical) {
                return $instance->getUriRecursive();
            }

            if ($instance instanceof Model) {
                return $instance->uri;
            }
        }

        return;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setAliases(array $value)
    {
        $this->aliases = $value;

        return $this;
    }

    public function setRedirect($value)
    {
        $this->redirect = $value;

        return $this;
    }

    public function setStatus($value)
    {
        $this->status = $value;

        return $this;
    }

    protected function toArray()
    {
        $data = $this->data;

        foreach ($this->aliases as $source => $alias) {
            if (isset($data[$source]) && !isset($data[$alias])) {
                $data[$alias] = $data[$source];
                unset($data[$source]);
            }
        }

        return $data;
    }
}
