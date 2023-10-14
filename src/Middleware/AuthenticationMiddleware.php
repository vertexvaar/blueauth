<?php

declare(strict_types=1);

namespace VerteXVaaR\BlueAuth\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use VerteXVaaR\BlueSprints\Environment\Config;
use VerteXVaaR\BlueSprints\Environment\Paths;

use function CoStack\Lib\concat_paths;
use function file_exists;

readonly class AuthenticationMiddleware implements MiddlewareInterface
{
    public function __construct(
        private Config $config,
        private Paths $paths
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $authenticated = false;
        $username = null;

        $cookies = $request->getCookieParams();
        $cookieAuthName = $this->config->cookieAuthName ?: 'bluedist_auth';
        $authCookie = $cookies[$cookieAuthName] ?? null;

        if (null !== $authCookie) {
            $authPath = concat_paths($this->paths->database, 'auth');
            $cookieFile = concat_paths($authPath, $authCookie);
            if (file_exists($cookieFile)) {
                $sessionValues = require $cookieFile;
                if ($sessionValues['authenticated'] ?? false) {
                    $authenticated = true;
                    $username = $sessionValues['username'] ?? null;
                }
            }
        }

        $request = $request->withAttribute('authenticated', $authenticated);
        $request = $request->withAttribute('username', $username);

        return $handler->handle($request);
    }
}
