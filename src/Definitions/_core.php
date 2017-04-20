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

    'monolog.lineformat' => "[%datetime%] %level_name%: %message% %context% %extra%\n",

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

    Monolog\Formatter\LineFormatter::class
    => object()
        ->constructorParameter('format', get('monolog.lineformat'))
        ->constructorParameter('allowInlineLineBreaks', true),

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

        $whoops->register();

        return $whoops;
    },

    Filesystem::class
    => object(League\Flysystem\Filesystem::class),

    Flash::class
    => object(Mopsis\Extensions\Flash::class),

    HttpClient::class
    => object(Http\Adapter\Guzzle6\Client::class),

    Json::class
    => object(Mopsis\Extensions\Json::class),

    Logger::class
    => function (ContainerInterface $c) {
        $logger = new Monolog\Logger('default');

        $logger->pushHandler(new Monolog\Handler\ChromePHPHandler(Monolog\Logger::INFO));
        $logger->pushHandler($c->get(MonologNoticeHandler::class));
        $logger->pushHandler($c->get(MonologErrorHandler::class));
        $logger->pushHandler(new Monolog\Handler\PushoverHandler(
            'aw6zvva5hvy67Y1gvnagx7y3GZzEDA',
            'uF1VyiRtDd1XXnEKA41imF2P88gxJ4',
            config('project.title'),
            Monolog\Logger::ERROR,
            false
        ));

        return $logger;
    },

    Mailgun::class
    => object(Mailgun\Mailgun::class)
        ->constructorParameter('httpClient', get('HttpClient')),

    MonologErrorHandler::class
    => object(Monolog\Handler\StreamHandler::class)
        ->constructor(dot('app.error_log'), Monolog\Logger::ERROR, false)
        ->method('setFormatter', get(Monolog\Formatter\LineFormatter::class)),

    MonologNoticeHandler::class
    => object(Monolog\Handler\StreamHandler::class)
        ->constructor(dot('app.application_log'), Monolog\Logger::NOTICE, false)
        ->method('setFormatter', get(Monolog\Formatter\LineFormatter::class)),

    Renderer::class
    => object(Twig_Environment::class),

    Translator::class
    => object(Illuminate\Translation\Translator::class)
        ->constructorParameter('locale', dot('translator.locale')),

    Xml::class
    => object(Mopsis\Extensions\SimpleXML\SimpleXMLElement::class)
];
