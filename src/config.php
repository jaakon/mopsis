<?php

use function DI\factory;
use function DI\link;
use function DI\object;

return [
	'flysystem.local.config' => 'storage/files',
	'monolog.lineformat'     => "[%datetime%] %level_name%: %message% %context% %extra%\n",
	'stash.apc.config'       => [
		'ttl'       => 3600,
		'namespace' => md5($_SERVER['HTTP_HOST'])
	],
	'stash.redis.config'     => [],
	'twig.dev.config'        => [
		'cache'            => false,
		'auto_reload'      => true,
		'autoescape'       => true,
		'strict_variables' => false
	],
	'twig.live.config'       => [
		'cache'            => 'storage/cache/',
		'auto_reload'      => true,
		'autoescape'       => true,
		'strict_variables' => false
	],

	\Aptoma\Twig\Extension\MarkdownEngineInterface::class => object(\Mopsis\Twig\Extensions\Markdown\MarkdownEngine::class),

	\Asm89\Twig\CacheExtension\CacheProviderInterface::class => object(\Mopsis\Twig\Extensions\Cache\CacheAdapter::class),

	\Asm89\Twig\CacheExtension\CacheStrategyInterface::class => object(\Asm89\Twig\CacheExtension\CacheStrategy\GenerationalCacheStrategy::class),

	\Asm89\Twig\CacheExtension\CacheStrategy\KeyGeneratorInterface::class => object(\Mopsis\Twig\Extensions\Cache\KeyGenerator::class),

	\League\Flysystem\AdapterInterface::class => object(\League\Flysystem\Adapter\Local::class)
		->constructor(link('flysystem.local.config')),

	\League\Flysystem\CacheInterface::class => object(\League\Flysystem\Cache\Stash::class)
		->constructor(link('cache')),

	\Monolog\Formatter\LineFormatter::class => object()
		->constructorParameter('format', link('monolog.lineformat'))
		->constructorParameter('allowInlineLineBreaks', true),

	\Mopsis\Validation\Request\BasicRequest::class => object(\Mopsis\Validation\Request\RawRequest::class),

	\Mopsis\Core\User::class => factory(function () {
		return Mopsis\Auth::user();
	}),

	\Psr\Log\LoggerInterface::class => link('logger'),

	\Stash\Interfaces\PoolInterface::class => link('cache'),

	\Stash\Driver\Apc::class => object()
		->method('setOptions', link('stash.apc.config')),

	\Stash\Driver\Redis::class => object()
		->method('setOptions', link('stash.redis.config')),

	\Twig_Environment::class => object()
		->constructor(new \Twig_Loader_Filesystem(['application/views']), link('twig.live.config'))
		->method('addExtension', link(\Asm89\Twig\CacheExtension\Extension::class))
		->method('addExtension', link(\Mopsis\Twig\Extensions\Markdown::class)),

	\Whoops\Handler\PlainTextHandler::class => object()
		->constructor(link('logger')),

	\Whoops\Handler\PrettyPageHandler::class => object()
		->method('setEditor', 'sublime'),

	'bouncer' => object(\Helpers\Bouncer::class),

	'cache' => object(\Stash\Pool::class)
		->constructor(link(Stash\Driver\Redis::class))
		->method('setNamespace', md5($_SERVER['HTTP_HOST'])),

	'database' => factory(function () {
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
	}),

	'errorHandler' => factory(function(DI\ContainerInterface $c) {
		$whoops = new \Whoops\Run;
		$whoops->pushHandler($c->get(\Whoops\Handler\PrettyPageHandler::class));
		$whoops->pushHandler($c->get(\Whoops\Handler\PlainTextHandler::class));
		$whoops->register();

		return $whoops;
	}),

	'filesystem' => object(\League\Flysystem\Filesystem::class),

	'logger' => factory(function (DI\ContainerInterface $c) {
		$errorHandler  = new Monolog\Handler\StreamHandler(CORE_ERROR_LOG, Monolog\Logger::ERROR, false);
		$errorHandler->setFormatter($c->get(\Monolog\Formatter\LineFormatter::class));

		$noticeHandler = new Monolog\Handler\StreamHandler(CORE_APPLICATION_LOG, Monolog\Logger::NOTICE, false);
		$noticeHandler->setFormatter($c->get(\Monolog\Formatter\LineFormatter::class));

		$logger = new Monolog\Logger('default');

		$logger->pushHandler(new Monolog\Handler\ChromePHPHandler(Monolog\Logger::DEBUG));
		$logger->pushHandler($noticeHandler);
		$logger->pushHandler($errorHandler);
		$logger->pushHandler(new Monolog\Handler\PushoverHandler('aw6zvva5hvy67Y1gvnagx7y3GZzEDA', 'uF1VyiRtDd1XXnEKA41imF2P88gxJ4', DEFAULT_TITLE, Monolog\Logger::ERROR, false));

		return $logger;
	})
];
