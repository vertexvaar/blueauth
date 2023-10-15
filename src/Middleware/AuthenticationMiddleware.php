<?php

declare(strict_types=1);

namespace VerteXVaaR\BlueAuth\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use VerteXVaaR\BlueAuth\Service\AuthenticationService;

readonly class AuthenticationMiddleware implements MiddlewareInterface
{
    public function __construct(
        private AuthenticationService $authenticationService,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $session = $this->authenticationService->loadSessionFromRequest($request);

        $request = $request->withAttribute('session', $session);

        return $handler->handle($request);
    }
}
