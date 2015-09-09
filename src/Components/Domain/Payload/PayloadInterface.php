<?php namespace Mopsis\Components\Domain\Payload;

interface PayloadInterface
{
	public function add(array $data);

	public function get($key = null);

	public function getName();
}
