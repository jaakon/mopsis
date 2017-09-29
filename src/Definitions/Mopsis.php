<?php

use function DI\dot;
use function DI\get;
use function DI\object;

return [
    Mopsis\Components\View::class
    => object()
        ->constructorParameter('extensions', get('twig.extensions')),

    Mopsis\Contracts\User::class
    => function () {
        return Mopsis\Core\Auth::user();
    },

    Mopsis\Contracts\Payload::class
    => object(Mopsis\Components\Payload\NotImplemented::class)
        ->constructor(),

    Mopsis\FormBuilder\FormBuilder::class
    => object()
        ->constructorParameter('xmlData', dot('app.forms')),

    Mopsis\FormBuilder\RulesProvider::class
    => object()
        ->constructorParameter('xmlData', dot('app.forms'))
];
