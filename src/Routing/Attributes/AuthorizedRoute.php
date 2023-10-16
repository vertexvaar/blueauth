<?php

declare(strict_types=1);

namespace VerteXVaaR\BlueAuth\Routing\Attributes;

use Attribute;
use VerteXVaaR\BlueSprints\Routing\Attributes\Route;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
readonly class AuthorizedRoute extends Route
{
    public function __construct(
        string $path,
        string $method = 'GET',
        int $priority = 100,
        public bool $requireAuthorization = false,
        public array $requiredRoles = [],
    ) {
        parent::__construct($path, $method, $priority);
    }
}
