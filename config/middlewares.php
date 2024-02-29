<?php

declare(strict_types=1);

use VerteXVaaR\BlueAuth\Middleware\AuthenticationMiddleware;
use VerteXVaaR\BlueAuth\Middleware\AuthorizationMiddleware;
use VerteXVaaR\BlueAuth\Middleware\LoginRedirectionMiddleware;

return [
    'vertexvaar/blueauth/authentication' => [
        'service' => AuthenticationMiddleware::class,
        'before' => ['vertexvaar/bluesprints/routing'],
    ],
    'vertexvaar/blueauth/loginredirect' => [
        'service' => LoginRedirectionMiddleware::class,
        'before' => ['vertexvaar/blueauth/authorization'],
        'after' => ['vertexvaar/blueauth/authentication', 'vertexvaar/bluesprints/routing'],
    ],
    'vertexvaar/blueauth/authorization' => [
        'service' => AuthorizationMiddleware::class,
        'after' => ['vertexvaar/bluesprints/routing', 'vertexvaar/blueauth/loginredirect'],
    ],
];
