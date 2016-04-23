<?php namespace Mopsis\Extensions\Twig\Cache;

use Mopsis\Extensions\Eloquent\Model;

class KeyGenerator implements \Asm89\Twig\CacheExtension\CacheStrategy\KeyGeneratorInterface
{
	public function generateKey($value)
	{
		if ($value instanceof Model) {
			return (string)$value . '_' . $value->updatedAt;
		}

		return null;
	}
}
