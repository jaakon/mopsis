<?php

return [
    'stash.apc.config' => ['ttl' => 3600, 'namespace' => md5($_SERVER['HTTP_HOST'])],
    'flysystem.local.config'     => 'storage/files',

    \Stash\Driver\Apc::class => DI\object()
    ->method('setOptions', DI\link('stash.apc.config')),

    League\Flysystem\AdapterInterface::class => DI\object('\League\Flysystem\Adapter\Local')
    ->constructor(DI\link('flysystem.local.config')),

    Psr\Log\LoggerInterface::class => DI\link('logger'),

    'bouncer' => DI\object('\Helpers\Bouncer'),
    'cache'   => DI\object('\Stash\Pool')->constructor(DI\link(Stash\Driver\Apc::class)),
    'capsule' => DI\factory(function () {
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
    'filesystem' => DI\object('\League\Flysystem\Filesystem'), //->constructorParameter('cache', new \League\Flysystem\Cache\Noop),
    'logger'     => DI\factory(function () {
        $logger = new Monolog\Logger('default');
        $logger->pushHandler(new Monolog\Handler\StreamHandler('storage/logs/application.log', Monolog\Logger::NOTICE));
        $logger->pushHandler(new Monolog\Handler\StreamHandler('storage/logs/error.log', Monolog\Logger::ERROR, false));
        $logger->pushHandler(new Monolog\Handler\ChromePHPHandler(Monolog\Logger::DEBUG));

        return $logger;
    }),

    Mopsis\Validation\Request\BasicRequest::class => DI\object('\Mopsis\Validation\Request\RawRequest'),

    Mopsis\Core\User::class => DI\factory(function () {
        return Mopsis\Auth::user();
    }),
];
