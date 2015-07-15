<?php namespace Mopsis\Core;

class Cache
{
	public static function get($key, callable $callback, $ttl = null)
	{
		$item  = \App::make('Cache')->getItem($key);
		$value = $item->get();

		if ($item->isMiss()) {
			$item->lock();
			$value = $callback();
			$item->set($value, $ttl);
		}

		return $value;
	}

	public static function clear($key)
	{
		return \App::make('Cache')->getItem($key)->clear();
	}
}
