<?php namespace Mopsis\Validation\Request;

class CleanRequest extends BasicRequest
{
	public function set($var, $value)
	{
		$clone = clone $this;

		if (strpos($var, '.') === false) {
			$clone->_data->{$var} = $value;
			return $clone;
		}

		$clone->_data = object_merge($clone->_data, $this->_anatomizeKeys((object) [$var => $value]));

		return $clone;
	}
}
