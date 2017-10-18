<?php

use function DI\autowire;
use function DI\create;
use function DI\get;

/*
DEBUG     = 100
INFO      = 200
NOTICE    = 250
WARNING   = 300
ERROR     = 400
CRITICAL  = 500
ALERT     = 550
EMERGENCY = 600
 */

return [
    'monolog.lineformat' => "[%datetime%] %level_name%: %message% %context% %extra%\n",

    Monolog\Formatter\LineFormatter::class
    => autowire()
        ->constructorParameter('format', get('monolog.lineformat'))
        ->constructorParameter('allowInlineLineBreaks', true)
        ->constructorParameter('ignoreEmptyContextAndExtra', true),

    Monolog\Logger::class
    => create()
        ->constructor('default')
        ->method('pushHandler', get(Monolog\DebugHandler::class))
        ->method('pushHandler', get(Monolog\InfoHandler::class))
        ->method('pushHandler', get(Monolog\ErrorHandler::class))
        ->method('pushHandler', get(Monolog\CriticalHandler::class)),

    Monolog\DebugHandler::class
    => create(Monolog\Handler\ChromePHPHandler::class)
        ->constructor(Monolog\Logger::DEBUG),

    Monolog\InfoHandler::class
    => create(Monolog\Handler\StreamHandler::class)
        ->constructor(get('app.application_log'), Monolog\Logger::INFO, false)
        ->method('setFormatter', get(Monolog\Formatter\LineFormatter::class)),

    Monolog\ErrorHandler::class
    => create(Monolog\Handler\StreamHandler::class)
        ->constructor(get('app.error_log'), Monolog\Logger::ERROR, false)
        ->method('setFormatter', get(Monolog\Formatter\LineFormatter::class)),

    Monolog\CriticalHandler::class
    => create(Monolog\Handler\PushoverHandler::class)
        ->constructor(
            'aw6zvva5hvy67Y1gvnagx7y3GZzEDA',
            'uF1VyiRtDd1XXnEKA41imF2P88gxJ4',
            env('APP_NAME'),
            Monolog\Logger::CRITICAL
        )
];
