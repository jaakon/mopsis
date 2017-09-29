<?php
namespace Mopsis\Components\Domain\Payload;

use Mopsis\Contracts\Hierarchical;
use Mopsis\Contracts\Model;

abstract class AbstractPayload implements PayloadInterface
{
    protected $data = [];

    protected $redirect;

    protected $status = 501;

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

    public function getName()
    {
        return lcfirst(array_pop(explode('\\', get_class($this))));
    }

    public function getRedirect()
    {
        return $this->redirect;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function override(PayloadInterface $payload)
    {
        return (new static($payload->get()))->add($this->get());
    }

    public function setRedirect($value)
    {
        $this->redirect = $value;
    }

    public function setStatus($value)
    {
        $this->status = $value;
    }
}
