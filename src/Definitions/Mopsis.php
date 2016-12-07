<?php

use function DI\dot;
use function DI\get;
use function DI\object;

return [
    'app' => [
        'forms'           => APPLICATION_PATH . '/config/forms.xml',
        'error_log'       => APPLICATION_PATH . '/storage/logs/error.log',
        'application_log' => APPLICATION_PATH . '/storage/logs/application.log',
        'views'           => ['resources/views']
    ],

    'config' => object(Mopsis\Core\Config::class),

    'classFormats' => [
        'Action'     => 'App\\{{MODULE}}\\Action\\{{SUBTYPE}}Action',
        'Collection' => 'App\\{{MODULE}}\\Collection',
        'Domain'     => 'App\\{{MODULE}}\\{{SUBTYPE}}',
        'Gateway'    => 'App\\{{MODULE}}\\Gateway',
        'Model'      => 'App\\{{MODULE}}\\Model',
        'Observer'   => 'App\\{{MODULE}}\\Observer',
        'Responder'  => 'App\\{{MODULE}}\\Responder\\{{SUBTYPE}}Responder',
        'View'       => '{{MODULE}}\\{{SUBTYPE}}'
    ],

    'static-pages' => [
        400 => __DIR__ . '/Resources/static-pages/bad-request-error',
        404 => __DIR__ . '/Resources/static-pages/not-found-error',
        500 => __DIR__ . '/Resources/static-pages/internal-server-error',
        502 => __DIR__ . '/Resources/static-pages/bad-gateway',
        503 => __DIR__ . '/Resources/static-pages/service-unavailable-error'
    ],

    'translator' => [
        'locale' => 'de',
        'path'   => APPLICATION_PATH . '/resources/lang/'
    ],

    Mopsis\Contracts\User::class
    => function () {
        return Mopsis\Core\Auth::user();
    },

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
