<?php

use Interop\Container\ContainerInterface as ContainerInterface;

use function DI\get;
use function DI\object;

return [
	'flysystem.local.config' => 'storage/files',
	'monolog.lineformat'     => "[%datetime%] %level_name%: %message% %context% %extra%\n",
	'namespacedModels'       => '\\App\\%1$s\\Domain\\%1$sModel',
	'stash.apc.config'       => [
		'ttl'       => 3600,
		'namespace' => md5($_SERVER['HTTP_HOST'])
	],
	'stash.redis.config'     => [],
	'stash.sqlite.config'    => [
		'path' => 'storage/cache/'
	],
	'translator.locale'      => 'de',
	'translator.path'        => 'resources/lang/',
	'twig.dev.config'        => [
		'debug'            => true,
		'cache'            => false,
		'auto_reload'      => true,
		'strict_variables' => true
	],
	'twig.live.config'       => [
		'debug'            => false,
		'cache'            => 'storage/cache/',
		'auto_reload'      => false,
		'strict_variables' => false
	],
	'twig.config'            => get('twig.dev.config'),
	'twigloader.config'      => ['app/views'],

	Aptoma\Twig\Extension\MarkdownEngineInterface::class
		=> object(\Mopsis\Twig\Extensions\Markdown\MarkdownEngine::class),

	Asm89\Twig\CacheExtension\CacheProviderInterface::class
		=> object(\Mopsis\Twig\Extensions\Cache\CacheAdapter::class),

	Asm89\Twig\CacheExtension\CacheStrategyInterface::class
		=> object(\Asm89\Twig\CacheExtension\CacheStrategy\GenerationalCacheStrategy::class),

	Asm89\Twig\CacheExtension\CacheStrategy\KeyGeneratorInterface::class
		=> object(\Mopsis\Twig\Extensions\Cache\KeyGenerator::class),

	Aura\Filter\FilterFactory::class
		=> object()
		->constructorParameter('validate_factories', [
			'bic' => function () {
				return app(\Mopsis\Filter\Rule\Validate\Bic::class);
			},
			'iban' => function () {
				return app(\Mopsis\Filter\Rule\Validate\Iban::class);
			},
			'money' => function () {
				return app(\Mopsis\Filter\Rule\Validate\Money::class);
			},
			'optional' => function () {
				return app(\Mopsis\Filter\Rule\Validate\Optional::class);
			},
			'zipcode' => function () {
				return app(\Mopsis\Filter\Rule\Validate\ZipCode::class);
			}
		])
		->constructorParameter('sanitize_factories', [
			'float' => function () {
				return app(\Mopsis\Filter\Rule\Sanitize\Float::class);
			}
		]),

	Aura\Filter\SubjectFilter::class
		=> function (ContainerInterface $c) {
			return $c->get(\Aura\Filter\FilterFactory::class)->newSubjectFilter();
		},

	Aura\Web\Request::class
		=> function (ContainerInterface $c) {
			return $c->get(\Aura\Web\WebFactory::class)->newRequest();
		},

	Aura\Web\Response::class
		=> function (ContainerInterface $c) {
			return $c->get(\Aura\Web\WebFactory::class)->newResponse();
		},

	Aura\Web\WebFactory::class
		=> function () {
			if (count($_POST) && !count($_FILES)) {
				// php://input is not available with enctype="multipart/form-data"
				// perhaps "enable_post_data_reading = off" can help?

				$_POST = [];

				foreach (explode('&', file_get_contents('php://input')) as $entry) {
					list($key,) = array_map('urldecode', explode('=', $entry));
					$key = preg_replace('/\[(.*)\]$/', '', $key);
					$_POST[$key] = trim($_REQUEST[str_replace(['.', ' '], '_', $key)]);
				}
			}

			return new \Aura\Web\WebFactory([
				'_COOKIE' => $_COOKIE,
				'_ENV'    => $_ENV,
				'_FILES'  => $_FILES,
				'_POST'   => $_POST,
				'_GET'    => $_GET,
				'_SERVER' => $_SERVER
			]);
		},

	Illuminate\Translation\LoaderInterface::class
		=> object(\Illuminate\Translation\FileLoader::class)
		->constructorParameter('path', get('translator.path')),

	League\Flysystem\AdapterInterface::class
		=> object(\League\Flysystem\Adapter\Local::class)
		->constructor(get('flysystem.local.config')),

	League\Flysystem\CacheInterface::class
		=> object(\League\Flysystem\Cache\Stash::class)
		->constructor(get('Cache')),

	Monolog\Formatter\LineFormatter::class
		=> object()
		->constructorParameter('format', get('monolog.lineformat'))
		->constructorParameter('allowInlineLineBreaks', true),

	Mopsis\Core\User::class
		=> function () {
			return Mopsis\Auth::user();
		},

	Mopsis\Core\View::class
		=> function (ContainerInterface $c) {
			$extensions = [
				$c->get(\Asm89\Twig\CacheExtension\Extension::class),
				$c->get(\Mopsis\Twig\Extensions\Formbuilder::class),
				$c->get(\Mopsis\Twig\Extensions\Markdown::class)
			];

			if ($c->get('twig.config')['debug']) {
				$extensions[] = $c->get(\Twig_Extension_Debug::class);
			}

			return new \Mopsis\Core\View($c->get(\Twig_Environment::class), $extensions);
		},

	Psr\Log\LoggerInterface::class
		=> get('Logger'),

	Stash\Interfaces\PoolInterface::class
		=> get('Cache'),

	Stash\Driver\Apc::class
		=> object()
		->method('setOptions', get('stash.apc.config')),

	Stash\Driver\Redis::class
		=> object()
		->method('setOptions', get('stash.redis.config')),

	Stash\Driver\Sqlite::class
		=> object()
		->method('setOptions', get('stash.sqlite.config')),

	Twig_LoaderInterface::class
		=> object(\Twig_Loader_Filesystem::class)
		->constructor(get('twigloader.config')),

	Twig_Environment::class
		=> object()
		->constructor(get(\Twig_LoaderInterface::class), get('twig.config')),

	Whoops\Handler\JsonResponseHandler::class
		=> object()
		->method('addTraceToOutput', true)
		->method('onlyForAjaxRequests', true),

	Whoops\Handler\PlainTextHandler::class
		=> object()
		->constructor(get('Logger')),

	Whoops\Handler\PrettyPageHandler::class
		=> object()
		->method('setEditor', 'sublime'),

	Cache::class
		=> object(\Stash\Pool::class)
		->constructor(get('StashDriver'))
		->method('setNamespace', md5($_SERVER['HTTP_HOST'])),

	Database::class
		=> function () {
			$manager = new \Illuminate\Database\Capsule\Manager;
			$manager->addConnection([
				'driver'    => SQL_DRIVER,
				'host'      => SQL_HOST,
				'database'  => SQL_DATABASE,
				'username'  => SQL_USERNAME,
				'password'  => SQL_PASSWORD,
				'charset'   => 'utf8',
				'collation' => 'utf8_general_ci',
				'prefix'    => ''
			]);

			$manager->setEventDispatcher(new \Illuminate\Events\Dispatcher);
			$manager->bootEloquent();
			$manager->setAsGlobal();

			return $manager;
		},

	ErrorHandler::class
		=> function (ContainerInterface $c) {
			$whoops = new \Whoops\Run;

			$whoops->pushHandler($c->get(\Whoops\Handler\PrettyPageHandler::class));
			$whoops->pushHandler($c->get(\Whoops\Handler\PlainTextHandler::class));
			$whoops->pushHandler($c->get(\Whoops\Handler\JsonResponseHandler::class));

			$whoops->register();

			return $whoops;
		},

	Filesystem::class
		=> object(\League\Flysystem\Filesystem::class),

	Flash::class
		=> object(\Mopsis\Core\Flash::class),

	Logger::class
		=> function (ContainerInterface $c) {
			$errorHandler = new Monolog\Handler\StreamHandler(CORE_ERROR_LOG, Monolog\Logger::ERROR, false);
			$errorHandler->setFormatter($c->get(\Monolog\Formatter\LineFormatter::class));

			$noticeHandler = new Monolog\Handler\StreamHandler(CORE_APPLICATION_LOG, Monolog\Logger::NOTICE, false);
			$noticeHandler->setFormatter($c->get(\Monolog\Formatter\LineFormatter::class));

			$logger = new Monolog\Logger('default');

			$logger->pushHandler(new Monolog\Handler\ChromePHPHandler(Monolog\Logger::DEBUG));
			$logger->pushHandler($noticeHandler);
			$logger->pushHandler($errorHandler);
			$logger->pushHandler(new Monolog\Handler\PushoverHandler('aw6zvva5hvy67Y1gvnagx7y3GZzEDA', 'uF1VyiRtDd1XXnEKA41imF2P88gxJ4', DEFAULT_TITLE, Monolog\Logger::ERROR, false));

			return $logger;
		},

	Renderer::class
		=> object(\Twig_Environment::class),

	StashDriver::class
		=> object(\Stash\Driver\Redis::class),

	Translator::class
		=> object(\Illuminate\Translation\Translator::class)
		->constructorParameter('locale', get('translator.locale'))
];
