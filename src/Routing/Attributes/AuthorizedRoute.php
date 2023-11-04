<?php

declare(strict_types=1);

namespace VerteXVaaR\BlueAuth\Routing\Attributes;

use Attribute;
use VerteXVaaR\BlueWeb\Routing\Attributes\Route;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
readonly class AuthorizedRoute extends Route
{
    /**
     * @noinspection MagicMethodsValidityInspection
     * @noinspection PhpMissingParentConstructorInspection
     */
    public function __construct(
        public string $path,
        public string $method = 'GET',
        public int $priority = 100,
        public bool $requireAuthorization = false,
        public array $requiredRoles = [],
    ) {
    }
}
