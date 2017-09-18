<?php

use function DI\dot;
use function DI\get;
use function DI\object;
use Interop\Container\ContainerInterface as ContainerInterface;

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
    => object()
        ->constructorParameter('format', get('monolog.lineformat'))
        ->constructorParameter('allowInlineLineBreaks', true)
        ->constructorParameter('ignoreEmptyContextAndExtra', true),

    Monolog\Logger::class
    => function (ContainerInterface $c) {
        $logger = new Monolog\Logger('default');

        $logger->pushHandler($c->get(Monolog\DebugHandler::class));
        $logger->pushHandler($c->get(Monolog\InfoHandler::class));
        $logger->pushHandler($c->get(Monolog\ErrorHandler::class));
        $logger->pushHandler($c->get(Monolog\CriticalHandler::class));

        return $logger;
    },
    Monolog\DebugHandler::class
    => object(Monolog\Handler\ChromePHPHandler::class)
        ->constructor(Monolog\Logger::DEBUG),

    Monolog\InfoHandler::class
    => object(Monolog\Handler\StreamHandler::class)
        ->constructor(dot('app.application_log'), Monolog\Logger::INFO, false)
        ->method('setFormatter', get(Monolog\Formatter\LineFormatter::class)),

    Monolog\ErrorHandler::class
    => object(Monolog\Handler\StreamHandler::class)
        ->constructor(dot('app.error_log'), Monolog\Logger::ERROR, false)
        ->method('setFormatter', get(Monolog\Formatter\LineFormatter::class)),

    Monolog\CriticalHandler::class
    => object(Monolog\Handler\PushoverHandler::class)
        ->constructor(
            'aw6zvva5hvy67Y1gvnagx7y3GZzEDA',
            'uF1VyiRtDd1XXnEKA41imF2P88gxJ4',
            env('APP_NAME'),
            Monolog\Logger::CRITICAL
        )
];
