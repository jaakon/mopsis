<?php

use function DI\create;
use function DI\get;

return [
    'stash.apc'        => [
        'ttl'       => 3600,
        'namespace' => md5($_SERVER['HTTP_HOST'])
    ],
    'stash.filesystem' => [
        'path' => APPLICATION_PATH . '/storage/cache/stash/'
    ],
    'stash.redis'      => [
        'servers' => [
            [
                'server' => env('REDIS_HOST', '127.0.0.1'),
                'port'   => env('REDIS_PORT', '6379'),
                'ttl'    => env('REDIS_TTL', 10)
            ]
        ]
    ],
    'stash.sqlite'     => ['path' => APPLICATION_PATH . '/storage/cache/'],

    Stash\Interfaces\PoolInterface::class
    => get('Cache'),

    Stash\Driver\Apc::class
    => create()
        ->constructor(get('stash.apc')),

    Stash\Driver\FileSystem::class
    => create()
        ->constructor(get('stash.filesystem')),

    Stash\Driver\Redis::class
    => create()
        ->constructor(get('stash.redis')),

    Stash\Driver\Sqlite::class
    => create()
        ->constructor(get('stash.sqlite')),

    StashDriver::class
    => get(
        array_shift(
            array_filter(
                [
                    'redis'    => Stash\Driver\Redis::class,
                    'apcu'     => Stash\Driver\Apc::class,
                    'sqlite3'  => Stash\Driver\Sqlite::class,
                    'standard' => Stash\Driver\FileSystem::class
                ],
                function ($name) {
                    return extension_loaded($name);
                },
                ARRAY_FILTER_USE_KEY
            )
        )
    )
];
