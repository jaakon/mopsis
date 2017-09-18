<?php

use function DI\dot;
use function DI\get;
use function DI\object;
use Interop\Container\ContainerInterface as ContainerInterface;

return [
    'app' => [
        'forms'           => APPLICATION_PATH . '/config/forms.xml',
        'error_log'       => APPLICATION_PATH . '/storage/logs/error.log',
        'application_log' => APPLICATION_PATH . '/storage/logs/application.log',
        'views'           => ['resources/html/pages', 'resources/html']
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

    'flysystem.local.config' => APPLICATION_PATH . '/storage/files',

    'static-pages' => [
        400 => FRAMEWORK_PATH . '/Resources/static-pages/bad-request-error',
        404 => FRAMEWORK_PATH . '/Resources/static-pages/not-found-error',
        500 => FRAMEWORK_PATH . '/Resources/static-pages/internal-server-error',
        502 => FRAMEWORK_PATH . '/Resources/static-pages/bad-gateway',
        503 => FRAMEWORK_PATH . '/Resources/static-pages/service-unavailable-error'
    ],

    'translator' => [
        'locale' => 'de',
        'path'   => APPLICATION_PATH . '/resources/lang/'
    ],

    League\Flysystem\AdapterInterface::class
    => object(League\Flysystem\Adapter\Local::class)
        ->constructor(get('flysystem.local.config')),

    Psr\Log\LoggerInterface::class
    => get('Logger'),

    Cache::class
    => object(Stash\Pool::class)
        ->constructor(get('StashDriver'))
        ->method('setNamespace', md5($_SERVER['HTTP_HOST']))
        ->method('setLogger', get('Logger')),

    Cookie::class
    => object(CodeZero\Cookie\VanillaCookie::class),

    Database::class
    => function (ContainerInterface $c) {
        $manager = $c->get(Illuminate\Database\Capsule\Manager::class);

        if (is_array(config('connections'))) {
            foreach (config('connections') as $name => $config) {
                $manager->addConnection($config, $name);
            }
        }

        $manager->setEventDispatcher(new Illuminate\Events\Dispatcher());
        $manager->bootEloquent();
        $manager->setAsGlobal();

        return $manager;
    },

    ErrorHandler::class
    => get(Whoops\Run::class),

    Filesystem::class
    => object(League\Flysystem\Filesystem::class),

    Flash::class
    => object(Mopsis\Extensions\Flash::class),

    HttpClient::class
    => object(Http\Adapter\Guzzle6\Client::class),

    Json::class
    => object(Mopsis\Extensions\Json::class),

    Logger::class
    => get(Monolog\Logger::class),

    Mailgun::class
    => object(Mailgun\Mailgun::class)
        ->constructorParameter('httpClient', get('HttpClient')),

    Renderer::class
    => object(Twig_Environment::class),

    Translator::class
    => object(Illuminate\Translation\Translator::class)
        ->constructorParameter('locale', dot('translator.locale')),

    Xml::class
    => object(Mopsis\Extensions\SimpleXML\SimpleXMLElement::class)
];
