<?php
namespace Mopsis\Components\Domain\Payload;

use Mopsis\Contracts\Model;
use Mopsis\Contracts\Hierarchical;

abstract class AbstractPayload implements PayloadInterface
{
    protected $payload = [];

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function add(array $data)
    {
        $this->payload = array_merge($this->payload, $data);

        return $this;
    }

    public function get($key = null)
    {
        if ($key === null) {
            return $this->payload;
        }

        if (isset($this->payload[$key])) {
            return $this->payload[$key];
        }

        if ($key === 'redirect' && isset($this->payload['instance'])) {
            if ($this->payload['instance'] instanceof Hierarchical) {
                return $this->payload['instance']->getUriRecursive();
            }

            if ($this->payload['instance'] instanceof Model) {
                return $this->payload['instance']->uri;
            }
        }

        return;
    }

    public function getName()
    {
        return implode('\\', array_slice(explode('\\', get_class($this)), -2));
    }
}
