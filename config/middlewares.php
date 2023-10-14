<?php

declare(strict_types=1);

use VerteXVaaR\BlueAuth\Middleware\AuthenticationMiddleware;

return [
    'vertexvaar/blueauth/authentication' => [
        'service' => AuthenticationMiddleware::class,
        'before' => ['vertexvaar/bluesprints/routing']
    ]
];
