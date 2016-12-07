<?php

use function DI\dot;
use function DI\get;
use function DI\object;

return [
    Illuminate\Container\Container::class
    => object()
        ->method('singleton', Illuminate\Contracts\Events\Dispatcher::class, Illuminate\Events\Dispatcher::class)
        ->method('singleton', Illuminate\Contracts\Debug\ExceptionHandler::class, Mopsis\Extensions\Illuminate\Debug\ExceptionHandler::class),

    Illuminate\Contracts\Events\Dispatcher::class
    => object(Illuminate\Events\Dispatcher::class),

    Illuminate\Database\Capsule\Manager::class
    => object()
        ->constructor(get(Illuminate\Container\Container::class)),

    Illuminate\Translation\LoaderInterface::class
    => object(Illuminate\Translation\FileLoader::class)
        ->constructorParameter('path', dot('translator.path'))
];
