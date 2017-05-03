<?php
namespace Mopsis\Core;

class Cache
{
    protected static $requests = 0;
    protected static $misses   = 0;

    public static function clear()
    {
        return App::get('Cache')->clear();
    }

    public static function getStats()
    {
        $successes = self::$requests - self::$misses;

        return (object)[
            'requests'    => self::$requests,
            'successes'   => $successes,
            'misses'      => self::$misses
        ];
    }

    public static function delete($key)
    {
        return App::get('Cache')->deleteItem(static::groupKeyFragments($key));
    }

    public static function get($key, callable $callback = null, $ttl = null)
    {
        self::$requests++;
        var_dump($key);

        $item  = App::get('Cache')->getItem(static::groupKeyFragments($key));
        $value = $item->get();

        if ($item->isMiss() && $callback !== null) {
            self::$misses++;

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
        /**
         * @var \Stash\Item $item
         */
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
