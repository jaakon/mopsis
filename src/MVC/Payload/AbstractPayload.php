<?php namespace Mopsis\MVC\Payload;

use Mopsis\Eloquent\Model;

abstract class AbstractPayload implements \Mopsis\MVC\PayloadInterface
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

		if ($key === 'redirect' && isset($this->payload['instance']) && $this->payload['instance'] instanceof Model) {
			return $this->payload['instance']->getUriRecursive();
		}

		return;
	}
}
