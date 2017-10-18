<?php

use function DI\autowire;
use function DI\create;
use function DI\factory;
use function DI\get;

return [
    Mopsis\Components\View::class
    => autowire()
        ->constructorParameter('extensions', get('twig.extensions')),

    Mopsis\Contracts\User::class
    => factory([Mopsis\Core\Auth::class, 'user']),

    Mopsis\Contracts\Payload::class
    => create(Mopsis\Components\Payload\NotImplemented::class),

    Mopsis\FormBuilder\FormBuilder::class
    => create()
        ->constructor(get('app.forms')),

    Mopsis\FormBuilder\RulesProvider::class
    => create()
        ->constructor(get('app.forms'))
];
