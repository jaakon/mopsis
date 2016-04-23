<?php namespace Mopsis\Validation\Specification;

class TwitterUser implements iValueSpecification
{
	private $_twitter;

	public function __construct(\Twitter $twitter)
	{
		$this->_twitter = $twitter;
	}

	public function isSatisfiedBy($value)
	{
		try {
			$this->_twitter->usersShow(null, $value);
			return true;
		} catch (\Exception $e) {
			return false;
		}
	}
}
