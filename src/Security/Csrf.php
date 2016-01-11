<?php namespace Mopsis\Security;

class Csrf
{
	public static function generateToken()
	{
		$key   = substr(sha1(uniqid(rand(), true)), 0, 8);
		$value = base64_encode(openssl_random_pseudo_bytes(32));

		$_SESSION['csrf'] = [
			'key'   => $key,
			'value' => $value
		];

		return (object) $_SESSION['csrf'];
	}
}
