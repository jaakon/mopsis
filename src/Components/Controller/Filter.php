<?php namespace Mopsis\Components\Controller;

use \Mopsis\Core\App;

class Filter extends \Mopsis\Components\Domain\AbstractFilter
{
	public function getResult($key = null)
	{
		return $key ? $this->result[$key] : $this->result;
	}
}
