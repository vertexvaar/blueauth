<?php

declare(strict_types=1);

namespace VerteXVaaR\BlueAuth\Middleware;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use VerteXVaaR\BlueAuth\Mvcr\Model\User;
use VerteXVaaR\BlueAuth\Routing\AuthorizedRoute;
use VerteXVaaR\BlueAuth\Service\AuthenticationService;
use VerteXVaaR\BlueSprints\Mvcr\Repository\Repository;

class AuthorizationMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly Repository $repository,
        private readonly AuthenticationService $authenticationService,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = $request->getAttribute('route');
        if ($route instanceof AuthorizedRoute && $route->requireAuthorization) {
            $session = $request->getAttribute('session');
            if (!$session->isAuthenticated()) {
                return new Response(403, [], 'Authentication required');
            }

            if (!empty($route->requiredRoles)) {
                $username = $session->getUsername();
                $user = $this->repository->findByIdentifier(User::class, $username);
                if (null === $user) {
                    $this->authenticationService->logout($session);
                    return new Response(403, [], 'Session expired');
                }
                if (!$user->hasRoles($route->requiredRoles)) {
                    return new Response(403, [], 'Authentication required');
                }
            }
        }

        return $handler->handle($request);
    }
}
