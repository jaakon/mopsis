<?php

use function DI\get;
use function DI\object;

return [
    Whoops\Handler\JsonResponseHandler::class
    => object()
        ->method('addTraceToOutput', true),

    Whoops\Handler\PlainTextHandler::class
    => object()
        ->constructor(get('Logger')),

    Whoops\Handler\PrettyPageHandler::class
    => object()
        ->method('setEditor', 'sublime')
];
