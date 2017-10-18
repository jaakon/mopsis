<?php

use function DI\autowire;
use function DI\get;
use Psr\Container\ContainerInterface;

return [
    'twig.config.development' => [
        'base_template_class' => 'Mopsis\Extensions\Twig\Template',
        'debug'               => true,
        'cache'               => false,
        'auto_reload'         => true,
        'strict_variables'    => true
    ],
    'twig.config.production'  => [
        'base_template_class' => 'Mopsis\Extensions\Twig\Template',
        'debug'               => false,
        'cache'               => APPLICATION_PATH . '/storage/cache/twig/',
        'auto_reload'         => false,
        'strict_variables'    => false
    ],

    'twig.config' => get('twig.config.' . env('APP_ENV')),

    'twig.extensions' => function (ContainerInterface $c) {
        $extensions = [
            $c->get(Mopsis\Extensions\Twig\FormBuilder::class)
        ];

        if (class_exists(Aptoma\Twig\Extension\MarkdownExtension::class)) {
            $extensions[] = $c->get(Mopsis\Extensions\Twig\Markdown::class);
        }

        if (class_exists(Asm89\Twig\CacheExtension\Extension::class)) {
            $extensions[] = $c->get(Asm89\Twig\CacheExtension\Extension::class);
        }

        if (class_exists(Twig_Extensions_Extension_Intl::class)) {
            $extensions[] = $c->get(Twig_Extensions_Extension_Intl::class);
        }

        if ($c->get('twig.config')['debug'] && class_exists(Twig_Extension_Debug::class)) {
            $extensions[] = $c->get(Twig_Extension_Debug::class);
        }

        return $extensions;
    },

    Twig_Environment::class
    => autowire()
        ->constructor(get(Twig_LoaderInterface::class), get('twig.config')),

    Twig_LoaderInterface::class
    => autowire(Twig_Loader_Filesystem::class)
        ->constructor(get('app.views'))
];
