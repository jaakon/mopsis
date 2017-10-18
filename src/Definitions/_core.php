<?php

use function DI\autowire;
use function DI\create;
use function DI\get;

return [
    'app.forms'           => APPLICATION_PATH . '/config/forms.xml',
    'app.error_log'       => APPLICATION_PATH . '/storage/logs/error.log',
    'app.application_log' => APPLICATION_PATH . '/storage/logs/application.log',
    'app.views'           => ['resources/html/pages', 'resources/html'],

    'config' => autowire(Mopsis\Core\Config::class),

    'classFormats' => [
        'Action'     => 'App\\{{MODULE}}\\Action\\{{SUBTYPE}}Action',
        'Responder'  => 'App\\{{MODULE}}\\Responder\\{{SUBTYPE}}Responder',
        'Model'      => 'App\\{{MODULE}}\\{{MODULE}}Model',
        'Collection' => 'App\\{{MODULE}}\\{{MODULE}}Collection',
        'Observer'   => 'App\\{{MODULE}}\\{{MODULE}}Observer'
    ],

    'flysystem.local.config' => APPLICATION_PATH . '/storage/files',

    'static-pages' => [
        400 => FRAMEWORK_PATH . '/Resources/static-pages/bad-request-error',
        404 => FRAMEWORK_PATH . '/Resources/static-pages/not-found-error',
        500 => FRAMEWORK_PATH . '/Resources/static-pages/internal-server-error',
        502 => FRAMEWORK_PATH . '/Resources/static-pages/bad-gateway',
        503 => FRAMEWORK_PATH . '/Resources/static-pages/service-unavailable-error'
    ],

    'translator.locale' => 'de',
    'translator.path'   => APPLICATION_PATH . '/resources/lang/',

    League\Flysystem\AdapterInterface::class
    => autowire(League\Flysystem\Adapter\Local::class)
        ->constructor(get('flysystem.local.config')),

    Psr\Log\LoggerInterface::class
    => get('Logger'),

    Cache::class
    => create(Stash\Pool::class)
        ->constructor(get('StashDriver'))
        ->method('setNamespace', md5($_SERVER['HTTP_HOST']))
        ->method('setLogger', get('Logger')),

    Cookie::class
    => autowire(CodeZero\Cookie\VanillaCookie::class),

    Database::class
    => function (Illuminate\Database\Capsule\Manager $manager) {
        \Illuminate\Database\Connection::resolverFor('mysql', function (...$args) {
            return new \Mopsis\Extensions\Illuminate\Database\MariaDbConnection(...$args);
        });

        if (is_array(config('connections'))) {
            foreach (config('connections') as $name => $config) {
                $manager->addConnection($config, $name);
            }
        }

        return $manager;
    },

    ErrorHandler::class
    => get(Whoops\Run::class),

    Filesystem::class
    => autowire(League\Flysystem\Filesystem::class),

    Flash::class
    => autowire(Mopsis\Extensions\Flash::class),

    Json::class
    => autowire(Mopsis\Extensions\Json::class),

    Logger::class
    => get(Monolog\Logger::class),

    Renderer::class
    => autowire(Twig_Environment::class),

    Translator::class
    => autowire(Illuminate\Translation\Translator::class)
        ->constructorParameter('locale', get('translator.locale'))
];
