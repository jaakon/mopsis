<?php namespace Mopsis\Validation\Specification;

class PushBulletApiKey implements iValueSpecification
{
	public function isSatisfiedBy($value)
	{
		$result = send_http_request('GET', 'https://'.$value.':null@www.pushbullet.com/api/devices');
		return $result[0]['http_code'] === 200;
	}
}
