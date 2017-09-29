<?php

use function DI\dot;
use function DI\get;
use function DI\object;

return [
    Mopsis\Contracts\User::class
    => function () {
        return Mopsis\Core\Auth::user();
    },

    Mopsis\Components\Domain\Payload\PayloadInterface::class
    => object(Mopsis\Components\Domain\Payload\NotImplemented::class)
        ->constructor([]),

    Mopsis\Components\View\View::class
    => object()
        ->constructorParameter('extensions', get('twig.extensions')),

    Mopsis\FormBuilder\FormBuilder::class
    => object()
        ->constructorParameter('xmlData', dot('app.forms')),

    Mopsis\FormBuilder\RulesProvider::class
    => object()
        ->constructorParameter('xmlData', dot('app.forms'))
];
