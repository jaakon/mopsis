<?php namespace Mopsis\Core;

class Cache
{
	public static function delete($key)
	{
		return App::get('Cache')->deleteItem(static::groupKeyFragments($key));
	}

	public static function clear()
	{
		return App::get('Cache')->clear();
	}

	public static function get($key, callable $callback = null, $ttl = null)
	{
		/** @var \Stash\Item $item */
		$item  = App::get('Cache')->getItem(static::groupKeyFragments($key));
		$value = $item->get();

		if ($item->isMiss() && $callback !== null) {
			$item->lock();

			$value = $callback();

			$item->set($value);

			if ($ttl !== null) {
				$item->expiresAfter($ttl);
			}

			App::get('Cache')->save($item);
		}

		return $value;
	}

	public static function set($key, $value, $ttl = null)
	{
		/** @var \Stash\Item $item */
		$item = App::get('Cache')->getItem(static::groupKeyFragments($key));

		$item->set($value);

		if ($ttl !== null) {
			$item->expiresAfter($ttl);
		}

		App::get('Cache')->save($item);
	}

	private static function groupKeyFragments($key)
	{
		return is_array($key) ? implode('/', $key) : $key;
	}
}
