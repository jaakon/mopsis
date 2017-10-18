<?php

use function DI\autowire;
use function DI\get;
use Psr\Container\ContainerInterface;

return [
    Aura\Accept\Accept::class
    => function (ContainerInterface $c) {
        return $c->get(Aura\Accept\AcceptFactory::class)->newInstance();
    },

    Aura\Accept\AcceptFactory::class
    => autowire()
        ->constructorParameter('server', $_SERVER),

    Aura\Filter\FilterFactory::class
    => autowire()
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
            'csrfToken'   => function () {
                return app(Mopsis\Extensions\Aura\Filter\Rule\Validate\CsrfToken::class);
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
/*
Aura\Filter\SubjectFilter::class
=> factory([Aura\Filter\FilterFactory::class, 'newSubjectFilter']),

Aura\Web\Request::class
=> factory([Aura\Web\WebFactory::class, 'newRequest']),

Aura\Web\Response::class
=> factory([Aura\Web\WebFactory::class, 'newResponse']),
 */
    Aura\Filter\SubjectFilter::class
    => function (ContainerInterface $c) {
        return $c->get(Aura\Filter\FilterFactory::class)->newSubjectFilter();
    },

    Aura\Web\Request::class
    => function (ContainerInterface $c) {
        return $c->get(Aura\Web\WebFactory::class)->newRequest();
    },

    Aura\Web\Response::class
    => function (ContainerInterface $c) {
        return $c->get(Aura\Web\WebFactory::class)->newResponse();
    },

    Aura\Web\WebFactory::class
    => function () {
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
    }

];
