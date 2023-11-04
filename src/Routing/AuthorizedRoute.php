<?php

declare(strict_types=1);

namespace VerteXVaaR\BlueAuth\Routing;

use VerteXVaaR\BlueWeb\Routing\Route;

readonly class AuthorizedRoute extends Route
{
    public function __construct(
        string $method,
        string $path,
        string $controller,
        string $action,
        public bool $requireAuthorization,
        public array $requiredRoles,
    ) {
        parent::__construct($method, $path, $controller, $action);
    }
}
