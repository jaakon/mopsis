<?php

use function DI\dot;
use function DI\get;
use function DI\object;

return [
    'stash' => [
        'apc'        => [
            'ttl'       => 3600,
            'namespace' => md5($_SERVER['HTTP_HOST'])
        ],
        'filesystem' => [
            'path' => APPLICATION_PATH . '/storage/cache/stash/'
        ],
        'redis'      => [
            'servers' => [
                [
                    'server' => '127.0.0.1',
                    'port'   => '6379',
                    'ttl'    => 10
                ]
            ]
        ],
        'sqlite'     => ['path' => APPLICATION_PATH . '/storage/cache/']
    ],

    Stash\Interfaces\PoolInterface::class
    => get('Cache'),

    Stash\Driver\Apc::class
    => object()
        ->constructor(dot('stash.apc')),

    Stash\Driver\FileSystem::class
    => object()
        ->constructor(dot('stash.filesystem')),

    Stash\Driver\Redis::class
    => object()
        ->constructor(dot('stash.redis')),

    Stash\Driver\Sqlite::class
    => object()
        ->constructor(dot('stash.sqlite')),

    StashDriver::class
    => object(
        array_shift(
            array_filter(
                [
                    'redis'    => \Stash\Driver\Redis::class,
                    'apcu'     => \Stash\Driver\Apc::class,
                    'sqlite3'  => \Stash\Driver\Sqlite::class,
                    'standard' => \Stash\Driver\FileSystem::class
                ],
                function ($name) {
                    return extension_loaded($name);
                },
                ARRAY_FILTER_USE_KEY
            )
        )
    )
];
