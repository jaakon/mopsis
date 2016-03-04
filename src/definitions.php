<?php

use function DI\dot;
use function DI\get;
use function DI\object;
use Interop\Container\ContainerInterface as ContainerInterface;

/**
 * @noinspection PhpUndefinedClassInspection
 */
return [
    'app'                                                                => [
        'forms'           => APPLICATION_PATH . '/config/forms.xml',
        'error_log'       => APPLICATION_PATH . '/storage/logs/error.log',
        'application_log' => APPLICATION_PATH . '/storage/logs/application.log'
    ],

    'config'                                                             => object(Mopsis\Core\Config::class),

    'classFormats'                                                       => [
        'Action'     => 'App\\{{MODULE}}\\Action\\{{DOMAIN}}{{SUBTYPE}}Action',
        'Collection' => 'App\\{{MODULE}}\\Domain\\{{DOMAIN}}Collection',
        'Controller' => 'App\\{{MODULE}}\\Controller',
        'Domain'     => 'App\\{{MODULE}}\\Domain\\{{DOMAIN}}{{SUBTYPE}}',
        'Gateway'    => 'App\\{{MODULE}}\\Domain\\{{DOMAIN}}Gateway',
        'Model'      => 'App\\{{MODULE}}\\Domain\\{{DOMAIN}}Model',
        'Observer'   => 'App\\{{MODULE}}\\Domain\\{{DOMAIN}}Observer',
        'Responder'  => 'App\\{{MODULE}}\\Responder\\{{DOMAIN}}{{SUBTYPE}}Responder',
        'View'       => '{{MODULE}}\\{{SUBTYPE}}'
    ],

    'static-pages'                                                       => [
        400 => __DIR__ . '/Resources/static-pages/bad-request-error',
        404 => __DIR__ . '/Resources/static-pages/not-found-error',
        500 => __DIR__ . '/Resources/static-pages/internal-server-error',
        502 => __DIR__ . '/Resources/static-pages/bad-gateway',
        503 => __DIR__ . '/Resources/static-pages/service-unavailable-error'
    ],

    'flysystem.local.config'                                             => ApplicationPath . '/storage/files',

    'monolog.lineformat'                                                 => "[%datetime%] %level_name%: %message% %context% %extra%\n",

    'stash'                                                              => [
        'apc'        => [
            'ttl'       => 3600,
            'namespace' => md5($_SERVER['HTTP_HOST'])
        ],
        'filesystem' => [
            'path' => APPLICATION_PATH . '/storage/cache/stash/'
        ],
        'redis'      => [
            'servers' => [
                [
                    'server' => '127.0.0.1',
                    'port'   => '6379',
                    'ttl'    => 10
                ]
            ]
        ],
        'sqlite'     => ['path' => APPLICATION_PATH . '/storage/cache/']
    ],

    'translator'                                                         => [
        'locale' => 'de',
        'path'   => APPLICATION_PATH . '/resources/lang/'
    ],

    'twig'                                                               => [
        'config' => [
            'development' => [
                'base_template_class' => 'Mopsis\Extensions\Twig\Template',
                'debug'               => true,
                'cache'               => false,
                'auto_reload'         => true,
                'strict_variables'    => true
            ],
            'production'  => [
                'base_template_class' => 'Mopsis\Extensions\Twig\Template',
                'debug'               => false,
                'cache'               => APPLICATION_PATH . '/storage/cache/twig/',
                'auto_reload'         => false,
                'strict_variables'    => false
            ]
        ]
    ],

    'twig.config'                                                        => dot('twig.config.development'),
    'twig.extensions'                                                    => function (ContainerInterface $c) {
        $extensions = [
            $c->get(Asm89\Twig\CacheExtension\Extension::class),
            $c->get(Mopsis\Extensions\Twig\FormBuilder::class),
            $c->get(Mopsis\Extensions\Twig\Markdown::class)
        ];

        if (ClassExists(Twig_Extensions_Extension_Intl::class)) {
            $extensions[] = $c->get(Twig_Extensions_Extension_Intl::class);
        }

        if ($c->get('twig.config')['debug'] && class_exists(Twig_Extension_Debug::class)) {
            $extensions[] = $c->get(Twig_Extension_Debug::class);
        }

        return $extensions;
    },

    'twigloader.config'                                                  => ['resources/views'],

    Aptoma\Twig\Extension\MarkdownEngineInterface::class                 => object(Mopsis\Extensions\Twig\Markdown\MarkdownEngine::class),

    Asm89\Twig\CacheExtension\CacheProviderInterface::class              => object(Mopsis\Extensions\Twig\Cache\CacheAdapter::class),

    Asm89\Twig\CacheExtension\CacheStrategyInterface::class              => object(Asm89\Twig\CacheExtension\CacheStrategy\GenerationalCacheStrategy::class),

    Asm89\Twig\CacheExtension\CacheStrategy\KeyGeneratorInterface::class => object(Mopsis\Extensions\Twig\Cache\KeyGenerator::class),

    Aura\Filter\FilterFactory::class                                     => object()
        ->constructorParameter('validate_factories', [
            'after'       => function () {
                return app(Mopsis\Extensions\Aura\Filter\Rule\Validate\DateTime\After::class);
            },
            'before'      => function () {
                return app(Mopsis\Extensions\Aura\Filter\Rule\Validate\DateTime\Before::class);
            },
            'concurrent'  => function () {
                return app(Mopsis\Extensions\Aura\Filter\Rule\Validate\DateTime\Concurrent::class);
            },
            'duration'    => function () {
                return app(Mopsis\Extensions\Aura\Filter\Rule\Validate\DateTime\Duration::class);
            },
            'notAfter'    => function () {
                return app(Mopsis\Extensions\Aura\Filter\Rule\Validate\DateTime\NotAfter::class);
            },
            'notBefore'   => function () {
                return app(Mopsis\Extensions\Aura\Filter\Rule\Validate\DateTime\NotBefore::class);
            },
            'bic'         => function () {
                return app(Mopsis\Extensions\Aura\Filter\Rule\Validate\Finance\Bic::class);
            },
            'iban'        => function () {
                return app(Mopsis\Extensions\Aura\Filter\Rule\Validate\Finance\Iban::class);
            },
            'money'       => function () {
                return app(Mopsis\Extensions\Aura\Filter\Rule\Validate\Finance\Money::class);
            },
            'conditional' => function () {
                return app(Mopsis\Extensions\Aura\Filter\Rule\Validate\Conditional::class);
            },
            'decimal'     => function () {
                return app(Mopsis\Extensions\Aura\Filter\Rule\Validate\Decimal::class);
            },
            'optional'    => function () {
                return app(Mopsis\Extensions\Aura\Filter\Rule\Validate\Optional::class);
            },
            'zipcode'     => function () {
                return app(Mopsis\Extensions\Aura\Filter\Rule\Validate\ZipCode::class);
            }

        ])
        ->constructorParameter('sanitize_factories', [
            'array' => function () {
                return app(Mopsis\Extensions\Aura\Filter\Rule\Sanitize\ArrayValue::class);
            },
            'float' => function () {
                return app(Mopsis\Extensions\Aura\Filter\Rule\Sanitize\FloatValue::class);
            }

        ]),

    Aura\Filter\SubjectFilter::class                                     => function (ContainerInterface $c) {
        return $c->get(Aura\Filter\FilterFactory::class)->newSubjectFilter();
    },

    Aura\Web\Request::class                                              => function (ContainerInterface $c) {
        return $c->get(Aura\Web\WebFactory::class)->newRequest();
    },

    Aura\Web\Response::class                                             => function (ContainerInterface $c) {
        return $c->get(Aura\Web\WebFactory::class)->newResponse();
    },

    Aura\Web\WebFactory::class                                           => function () {
        if (count($_POST) && !count($_FILES)) {
// php://input is not available with enctype="multipart/form-data"
            // perhaps "enable_post_data_reading = off" can help?

            $_POST = [];

            foreach (explode('&', file_get_contents('php://input')) as $entry) {
                list($key)   = array_map('urldecode', explode('=', $entry));
                $key         = preg_replace('/\[(.*)\]$/', '', $key);
                $_POST[$key] = $_REQUEST[str_replace(['.', ' '], '_', $key)];

                if (!is_array($_POST[$key])) {
                    $_POST[$key] = trim($_POST[$key]);
                }
            }
        }

        return new Aura\Web\WebFactory([
            '_COOKIE' => $_COOKIE,
            '_ENV'    => $_ENV,
            '_FILES'  => $_FILES,
            '_POST'   => $_POST,
            '_GET'    => $_GET,
            '_SERVER' => $_SERVER
        ]);
    },

    Illuminate\Translation\LoaderInterface::class                        => object(Illuminate\Translation\FileLoader::class)
        ->constructorParameter('path', dot('translator.path')),

    League\Flysystem\AdapterInterface::class                             => object(League\Flysystem\Adapter\Local::class)
        ->constructor(get('flysystem.local.config')),

    Monolog\Formatter\LineFormatter::class                               => object()
        ->constructorParameter('format', get('monolog.lineformat'))
        ->constructorParameter('allowInlineLineBreaks', true),

    Mopsis\Contracts\User::class                                         => function () {
        return Mopsis\Core\Auth::user();
    },

    Mopsis\Components\View\View::class                                   => object()
        ->constructorParameter('extensions', get('twig.extensions')),

    Mopsis\FormBuilder\FormBuilder::class                                => object()
        ->constructorParameter('xmlData', dot('app.forms')),

    Mopsis\FormBuilder\RulesProvider::class                              => object()
        ->constructorParameter('xmlData', dot('app.forms')),

    Psr\Log\LoggerInterface::class                                       => get('Logger'),

    Stash\Interfaces\PoolInterface::class                                => get('Cache'),

    Stash\Driver\Apc::class                                              => object()
        ->method('setOptions', dot('stash.apc')),

    Stash\Driver\FileSystem::class                                       => object()
        ->method('setOptions', dot('stash.filesystem')),

    Stash\Driver\Redis::class                                            => object()
        ->method('setOptions', dot('stash.redis')),

    Stash\Driver\Sqlite::class                                           => object()
        ->method('setOptions', dot('stash.sqlite')),

    Twig_Environment::class                                              => object()
        ->constructor(get(Twig_LoaderInterface::class), get('twig.config'))
        ->method('addTokenParser', get(Twig_TokenParser_Include::class)),

    TwigLoaderinterface::class                                           => object(Twig_Loader_Filesystem::class)
        ->constructor(get('twigloader.config')),

    Twig_TokenParser_Include::class                                      => object(Mopsis\Extensions\Twig\TokenParser\IncludeToken::class),

    Whoops\Handler\JsonResponseHandler::class                            => object()
        ->method('addTraceToOutput', true),

    Whoops\Handler\PlainTextHandler::class                               => object()
        ->constructor(get('Logger')),

    Whoops\Handler\PrettyPageHandler::class                              => object()
        ->method('setEditor', 'sublime'),

    Cache::class                                                         => object(Stash\Pool::class)
        ->constructor(get('StashDriver'))
        ->method('setNamespace', md5($_SERVER['HTTP_HOST']))
        ->method('setLogger', get('Logger')),

    CacheTool::class                                                     => function () {
        $adapter = new CacheTool\Adapter\FastCGI('127.0.0.1:9000');

        return CacheTool\CacheTool::factory($adapter);
    },

    Cookie::class                                                        => object(CodeZero\Cookie\VanillaCookie::class),

    Database::class                                                      => function () {
        $manager = new Illuminate\Database\Capsule\Manager();

        foreach (config('connections') as $name => $config) {
            $manager->addConnection($config, $name);
        }

        $manager->setEventDispatcher(new Illuminate\Events\Dispatcher());
        $manager->bootEloquent();
        $manager->setAsGlobal();

        return $manager;
    },

    ErrorHandler::class                                                  => function (ContainerInterface $c) {
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

    Filesystem::class                                                    => object(League\Flysystem\Filesystem::class),

    Flash::class                                                         => object(Mopsis\Extensions\Flash::class),

    Json::class                                                          => object(Mopsis\Extensions\Json::class),

    Logger::class                                                        => function (ContainerInterface $c) {
        $logger = new Monolog\Logger('default');

        $logger->pushHandler(new Monolog\Handler\ChromePHPHandler(Monolog\Logger::INFO));
        $logger->pushHandler($c->get(MonologNoticeHandler::class));
        $logger->pushHandler($c->get(MonologErrorHandler::class));
        $logger->pushHandler(new Monolog\Handler\PushoverHandler('aw6zvva5hvy67Y1gvnagx7y3GZzEDA', 'uF1VyiRtDd1XXnEKA41imF2P88gxJ4', config('project.title'), Monolog\Logger::ERROR, false));

        return $logger;
    },

    MonologErrorHandler::class                                           => object(Monolog\Handler\StreamHandler::class)
        ->constructor(dot('app.error_log'), Monolog\Logger::ERROR, false)
        ->method('setFormatter', get(Monolog\Formatter\LineFormatter::class)),

    MonologNoticeHandler::class                                          => object(Monolog\Handler\StreamHandler::class)
        ->constructor(dot('app.application_log'), Monolog\Logger::NOTICE, false)
        ->method('setFormatter', get(Monolog\Formatter\LineFormatter::class)),

    Renderer::class                                                      => object(Twig_Environment::class),

    StashDriver::class                                                   => object(array_filter([
        'redis'    => \Stash\Driver\Redis::class,
        'apc'      => \Stash\Driver\Apc::class,
        'sqlite3'  => \Stash\Driver\Sqlite::class,
        'standard' => \Stash\Driver\FileSystem::class
    ], function ($name) {
        return ExtensionLoaded($name);
    }, ARRAY_FILTER_USE_KEY)[0]),

    Translator::class                                                    => object(Illuminate\Translation\Translator::class)
        ->constructorParameter('locale', dot('translator.locale'))
];
