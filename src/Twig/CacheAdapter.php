<?php namespace Mopsis\Twig;

class CacheAdapter implements \Asm89\Twig\CacheExtension\CacheProviderInterface
{
	private $cache;

	public function __construct(\Stash\Interfaces\PoolInterface $cache)
	{
		$this->cache = $cache;
	}

	public function fetch($key)
	{
		$item = $this->cache->getItem($key);
		$data = $item->get();

		return $item->isMiss() ? false : $data;
	}

	public function save($key, $value, $ttl = 0)
	{
		$this->cache->getItem($key)->set($value, $ttl ?: null);
	}
}
