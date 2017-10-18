<?php

use function DI\autowire;
use function DI\create;
use function DI\get;

return [
    Illuminate\Container\Container::class
    => autowire()
        ->method(
            'singleton',
            Illuminate\Contracts\Events\Dispatcher::class,
            Illuminate\Events\Dispatcher::class
        )
        ->method(
            'singleton',
            Illuminate\Contracts\Debug\ExceptionHandler::class,
            Mopsis\Extensions\Illuminate\Debug\ExceptionHandler::class
        ),

    Illuminate\Contracts\Events\Dispatcher::class
    => autowire(Illuminate\Events\Dispatcher::class),

    Illuminate\Database\Capsule\Manager::class
    => create()
        ->constructor(get(Illuminate\Container\Container::class))
        ->method('setEventDispatcher', create(Illuminate\Events\Dispatcher::class))
        ->method('bootEloquent')
        ->method('setAsGlobal'),

    Illuminate\Translation\LoaderInterface::class
    => autowire(Illuminate\Translation\FileLoader::class)
        ->constructorParameter('path', get('translator.path'))
];
