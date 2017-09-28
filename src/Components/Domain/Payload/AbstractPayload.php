<?php
namespace Mopsis\Components\Domain\Payload;

use Mopsis\Contracts\Hierarchical;
use Mopsis\Contracts\Model;

abstract class AbstractPayload implements PayloadInterface
{
    protected $data = [];

    protected $method;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function add(array $data)
    {
        $this->data = array_merge($this->data, $data);

        return $this;
    }

    public function get($key = null)
    {
        if ($key === null) {
            return $this->data;
        }

        if (isset($this->data[$key])) {
            return $this->data[$key];
        }

        if ($key === 'redirect' && isset($this->data['instance'])) {
            if ($this->data['instance'] instanceof Hierarchical) {
                return $this->data['instance']->getUriRecursive();
            }

            if ($this->data['instance'] instanceof Model) {
                return $this->data['instance']->uri;
            }
        }

        return;
    }

    public function getMethod()
    {
        if ($this->method === null) {
            $this->method = lcfirst(array_pop(explode('\\', get_class($this))));
        }

        return $this->method;
    }

    public function newInstance(array $data)
    {
        return (new static($data))->add($this->get());
    }

    public function setMethod($method)
    {
        $this->method = $method;
    }
}
