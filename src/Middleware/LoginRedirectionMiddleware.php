<?php

declare(strict_types=1);

namespace VerteXVaaR\BlueAuth\Middleware;

use GuzzleHttp\Psr7\CachingStream;
use GuzzleHttp\Psr7\LazyOpenStream;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Stream;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\SimpleCache\CacheInterface;
use VerteXVaaR\BlueAuth\Mvcr\Model\Session;
use VerteXVaaR\BlueAuth\Routing\Attributes\AuthorizedRoute;
use VerteXVaaR\BlueAuth\Service\AuthenticationService;
use VerteXVaaR\BlueWeb\Routing\Attributes\Route;
use VerteXVaaR\BlueWeb\Routing\RouteEncapsulation;

use function serialize;
use function unserialize;

class LoginRedirectionMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly AuthenticationService $authenticationService,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        if (
            $request->getUri()->getPath() === '/login'
            && $response->getStatusCode() === 303
        ) {
            $session = $request->getAttribute('session');
            $cacheKey = 'previousRequest.' . $session->identifier;
            if ($session->isAuthenticated() && $this->cache->has($cacheKey)) {
                $previousRequest = unserialize($this->cache->get($cacheKey), [
                    'allowed_classes' => [
                        ServerRequest::class,
                        Uri::class,
                        CachingStream::class,
                        LazyOpenStream::class,
                        Stream::class,
                        Session::class,
                        Route::class,
                        AuthorizedRoute::class,
                        RouteEncapsulation::class,
                    ],
                ]);
                $this->cache->delete($cacheKey);
                $previousRequest = $previousRequest->withAttribute('session', $session);
                return $handler->handle($previousRequest);
            }
            return new Response(303, ['Location' => '/']);
        }
        if ($response->getStatusCode() === 403) {
            $session = $request->getAttribute('session');
            if ($session->isAuthenticated()) {
                return $response;
            }
            $session = $this->authenticationService->forcePersistentSession($request);
            $cacheKey = 'previousRequest.' . $session->identifier;
            $this->cache->set($cacheKey, serialize($request));
            $response = new Response(303, ['Location' => '/login'], 'Login required');
        }
        return $response;
    }
}
