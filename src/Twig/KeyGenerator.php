<?php namespace Mopsis\Twig;

class KeyGenerator implements \Asm89\Twig\CacheExtension\CacheStrategy\KeyGeneratorInterface
{
	public function generateKey($value)
	{
		if ($value instanceof \Mopsis\Eloquent\Model) {
			return (string)$value . '_' . $value->updatedAt;
		}

		return null;
	}
}
