<?php
namespace Mopsis\Extensions\Twig\Cache;

use Asm89\Twig\CacheExtension\CacheProviderInterface;
use Stash\Interfaces\PoolInterface;

class CacheAdapter implements CacheProviderInterface
{
    private $cache;

    public function __construct(PoolInterface $cache)
    {
        $this->cache = $cache;
    }

    public function fetch($key)
    {
        /**
         * @noinspection PhpMethodParametersCountMismatchInspection
         */
        $item = $this->cache->getItem($key);
        $data = $item->get();

        return $item->isMiss() ? false : $data;
    }

    public function save($key, $value, $ttl = 0)
    {
        /**
         * @noinspection PhpMethodParametersCountMismatchInspection
         */
        $this->cache->getItem($key)->set($value, $ttl ?: null);
    }
}
