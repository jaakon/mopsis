<?php
namespace Mopsis\Console\Libraries;

class Schema
{
    public static function __callStatic($method, $args)
    {
        return Illuminate\Database\Capsule\Manager::schema()->{$method}(...$args);
    }
}
