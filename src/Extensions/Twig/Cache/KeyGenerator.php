<?php namespace Mopsis\Extensions\Twig\Cache;

class KeyGenerator implements \Asm89\Twig\CacheExtension\CacheStrategy\KeyGeneratorInterface
{
	public function generateKey($value)
	{
		if ($value instanceof \Mopsis\Components\Domain\Model\Model) {
			return (string)$value . '_' . $value->updatedAt;
		}

		return null;
	}
}
