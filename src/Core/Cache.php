<?php namespace Mopsis\Core;

use Mopsis\Core\App;

class Cache
{
	public static function clear($key)
	{
		return App::make('Cache')->getItem($key)->clear();
	}

	public static function flush()
	{
		return App::make('Cache')->flush();
	}

	public static function get($key, callable $callback = null, $ttl = null)
	{
		$item  = App::make('Cache')->getItem($key);
		$value = $item->get();

		if ($item->isMiss() && $callback !== null) {
			$item->lock();
			$value = $callback();
			$item->set($value, $ttl);
		}

		return $value;
	}

	public static function set($key, $value, $ttl = null)
	{
		App::make('Cache')->getItem($key)->set($value, $ttl);
	}
}
