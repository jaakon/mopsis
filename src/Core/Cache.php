<?php namespace Mopsis\Core;

class Cache
{
	public static function clear($key)
	{
		return \App::make('Cache')->getItem($key)->clear();
	}

	public static function flush()
	{
		return \App::make('Cache')->flush();
	}

	public static function get($key, callable $callback = null, $ttl = null)
	{
		$item  = \App::make('Cache')->getItem($key);
		$value = $item->get();

		if ($item->isMiss() && $callback !== null) {
			$item->lock();
			$value = $callback();
			$item->set($value, $ttl);
			debug($key, $value);
		}

		return $value;
	}
}
