<?php namespace Mopsis\ADR;

interface PayloadInterface
{
	public function add(array $data);
	public function get($key = null);
}
