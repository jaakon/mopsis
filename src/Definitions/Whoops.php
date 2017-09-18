<?php

use function DI\get;
use function DI\object;
use Interop\Container\ContainerInterface as ContainerInterface;

return [
    Whoops\Handler\JsonResponseHandler::class
    => object()
        ->method('addTraceToOutput', true),

    Whoops\Handler\PlainTextHandler::class
    => object()
        ->constructor(get('Logger')),

    Whoops\Handler\PrettyPageHandler::class
    => object()
        ->method('setEditor', 'sublime'),

    Whoops\Run::class
    => function (ContainerInterface $c) {
        $whoops = new Whoops\Run();

        $whoops->pushHandler($c->get(Whoops\Handler\PrettyPageHandler::class));

        if (Whoops\Util\Misc::isCommandLine()) {
            $whoops->pushHandler($c->get(Whoops\Handler\PlainTextHandler::class));
        }

        if (Whoops\Util\Misc::isAjaxRequest()) {
            $whoops->pushHandler($c->get(Whoops\Handler\JsonResponseHandler::class));
        }

        $whoops->pushHandler(function (Throwable $exception) use ($c) {
            $c->get(Logger::class)->error($exception->getMessage());
        });

        if (env('APP_ENV') === 'production' && !DEBUG) {
            $whoops->pushHandler(function (Throwable $exception) use ($c) {
                echo staticPage(502);
                return Whoops\Handler\Handler::QUIT;
            });
        }

        $whoops->register();

        return $whoops;
    }
];
