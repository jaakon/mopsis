<?php namespace Mopsis\MVC;

interface PayloadInterface
{
	public function add(array $data);
	public function get($key = null);
}
