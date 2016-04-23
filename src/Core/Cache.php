<?php
namespace Mopsis\Core;

use Mopsis\Core\App;

class Cache
{
    public static function clear($key)
    {
        return App::make('Cache')->deleteItem(static::groupKeyFragments($key));
    }

    public static function flush()
    {
        return App::make('Cache')->clear();
    }

    public static function get($key, callable $callback = null, $ttl = null)
    {
        /**
         * @var \Stash\Item $item
         */
        $item  = App::make('Cache')->getItem(static::groupKeyFragments($key));
        $value = $item->get();

        if ($item->isMiss() && $callback !== null) {
            $item->lock();

            $value = $callback();

            $item->set($value);

            if ($ttl !== null) {
                $item->expiresAfter($ttl);
            }

            App::make('Cache')->save($item);
        }

        return $value;
    }

    public static function set($key, $value, $ttl = null)
    {
        /**
         * @var \Stash\Item $item
         */
        $item = App::make('Cache')->getItem(static::groupKeyFragments($key));

        $item->set($value);

        if ($ttl !== null) {
            $item->expiresAfter($ttl);
        }

        App::make('Cache')->save($item);
    }

    private static function groupKeyFragments($key)
    {
        return is_array($key) ? implode('/', $key) : $key;
    }
}
