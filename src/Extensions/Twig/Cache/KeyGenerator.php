<?php
namespace Mopsis\Extensions\Twig\Cache;

use Asm89\Twig\CacheExtension\CacheStrategy\KeyGeneratorInterface;
use Mopsis\Extensions\Eloquent\Model;

class KeyGenerator implements KeyGeneratorInterface
{
    public function generateKey($value)
    {
        if ($value instanceof Model) {
            return (string) $value . '_' . $value->updatedAt;
        }

        return;
    }
}
