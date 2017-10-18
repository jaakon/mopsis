<?php

use function DI\autowire;

$definitions = [];

if (class_exists(Asm89\Twig\CacheExtension\Extension::class)) {
    $definitions = array_merge($definitions, [
        Asm89\Twig\CacheExtension\CacheProviderInterface::class
        => autowire(Mopsis\Extensions\Twig\Cache\CacheAdapter::class),

        Asm89\Twig\CacheExtension\CacheStrategyInterface::class
        => autowire(Asm89\Twig\CacheExtension\CacheStrategy\GenerationalCacheStrategy::class),

        Asm89\Twig\CacheExtension\CacheStrategy\KeyGeneratorInterface::class
        => autowire(Mopsis\Extensions\Twig\Cache\KeyGenerator::class)
    ]);
}

if (class_exists(Aptoma\Twig\Extension\MarkdownExtension::class)) {
    $definitions = array_merge($definitions, [
        Aptoma\Twig\Extension\MarkdownEngineInterface::class
        => autowire(Mopsis\Extensions\Twig\Markdown\MarkdownEngine::class)
    ]);
}

if (class_exists(CacheTool\CacheTool::class)) {
    $definitions = array_merge($definitions, [
        CacheTool::class
        => function () {
            $adapter = new CacheTool\Adapter\FastCGI('127.0.0.1:9000');

            return CacheTool\CacheTool::factory($adapter);
        }

    ]);
}

if (class_exists(Mailgun\Mailgun::class)) {
    $definitions = array_merge($definitions, [
        Mailgun::class
        => autowire(Mailgun\Mailgun::class)
            ->constructorParameter('httpClient', autowire(Http\Adapter\Guzzle6\Client::class))
    ]);
}

return $definitions;
