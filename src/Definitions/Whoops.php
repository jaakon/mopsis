<?php

use function DI\autowire;
use function DI\create;
use function DI\get;
use Psr\Container\ContainerInterface;

return [
    Whoops\Handler\JsonResponseHandler::class
    => autowire()
        ->method('addTraceToOutput', true),

    Whoops\Handler\PlainTextHandler::class
    => create()
        ->constructor(get('Logger')),

    Whoops\Handler\PrettyPageHandler::class
    => create()
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
