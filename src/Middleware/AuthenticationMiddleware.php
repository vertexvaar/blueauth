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
use function file_get_contents;
use function getenv;
use function json_decode;

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
        $cookieAuthName = $this->config->cookieAuthName ?: 'bluesprints_auth';
        $authCookie = $cookies[$cookieAuthName] ?? null;

        if (null !== $authCookie) {
            $authPath = concat_paths(getenv('VXVR_BS_ROOT'), $this->paths->database, 'auth');
            $cookieFile = concat_paths($authPath, $authCookie);
            if (file_exists($cookieFile)) {
                $sessionValues = json_decode(file_get_contents($cookieFile), true, 512, JSON_THROW_ON_ERROR);
                if ($sessionValues['authenticated'] ?? false) {
                    $authenticated = true;
                    $username = $sessionValues['username'] ?? null;
                }
            }
        }

        $request = $request->withAttribute('authenticated', $authenticated);
        $request = $request->withAttribute('username', $username);
        $request = $request->withAttribute('session', $authCookie);

        return $handler->handle($request);
    }
}
