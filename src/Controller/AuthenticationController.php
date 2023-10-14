<?php

declare(strict_types=1);

namespace VerteXVaaR\BlueAuth\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use RuntimeException;
use Twig\Environment;
use VerteXVaaR\BlueSprints\Environment\Config;
use VerteXVaaR\BlueSprints\Environment\Paths;
use VerteXVaaR\BlueSprints\Mvcr\Controller\AbstractController;
use VerteXVaaR\BlueSprints\Mvcr\Repository\Repository;
use VerteXVaaR\BlueSprints\Routing\Attributes\Route;

use function array_key_exists;
use function CoStack\Lib\concat_paths;
use function file_exists;
use function file_put_contents;
use function getenv;
use function is_dir;
use function json_encode;
use function mkdir;
use function setcookie;
use function unlink;

class AuthenticationController extends AbstractController
{
    public function __construct(
        Repository $repository,
        Environment $view,
        private readonly Paths $paths,
        private readonly Config $config
    ) {
        parent::__construct($repository, $view);
    }

    #[Route(path: '/login')]
    public function login(ServerRequestInterface $request): ResponseInterface
    {
        return $this->render('@vertexvaar_blueauth/login.html.twig');
    }

    #[Route(path: '/logout')]
    public function logout(ServerRequestInterface $request): ResponseInterface
    {
        $sessionIdentifier = $request->getAttribute('session');
        if ($sessionIdentifier) {
            $sessionFile = concat_paths(getenv('VXVR_BS_ROOT'), $this->paths->database, 'auth', $sessionIdentifier);
            if (file_exists($sessionFile)) {
                unlink($sessionFile);
            }
            setcookie($this->config->cookieAuthName ?: 'bluesprints_auth', '', -1, '/');
        }
        return $this->redirect('/');
    }

    #[Route(path: '/login', method: 'POST')]
    public function authenticate(ServerRequestInterface $request): ResponseInterface
    {
        $body = $request->getParsedBody();
        if (array_key_exists('username', $body) && array_key_exists('password', $body)) {
            $username = $body['username'];
            $password = $body['password'];
            if ($username === 'admin' && $password === 'password') {
                $path = concat_paths(getenv('VXVR_BS_ROOT'), $this->paths->database, 'auth');
                $sessionIdentifier = Uuid::uuid4()->toString();
                $session = [
                    'id' => $sessionIdentifier,
                    'username' => $username,
                    'authenticated' => true,
                ];
                if (!is_dir($path) && !mkdir($path, $this->config->folderPermissions, true) && !is_dir($path)) {
                    throw new RuntimeException(sprintf('Directory "%s" was not created', $path));
                }
                file_put_contents(concat_paths($path, $sessionIdentifier), json_encode($session, JSON_THROW_ON_ERROR));
                setcookie($this->config->cookieAuthName ?: 'bluesprints_auth', $sessionIdentifier);
                return $this->redirect('/');
            }
        }
        return $this->redirect('/login');
    }
}
