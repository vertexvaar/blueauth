<?php

declare(strict_types=1);

namespace VerteXVaaR\BlueAuth\Controller;

use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use VerteXVaaR\BlueSprints\Environment\Config;
use VerteXVaaR\BlueSprints\Environment\Paths;
use VerteXVaaR\BlueSprints\Mvc\AbstractController;
use VerteXVaaR\BlueSprints\Mvc\Repository;
use VerteXVaaR\BlueSprints\Mvc\TemplateRenderer;
use VerteXVaaR\BlueSprints\Utility\Strings;

use function array_key_exists;
use function CoStack\Lib\concat_paths;
use function file_exists;
use function file_put_contents;
use function getenv;
use function json_encode;
use function mkdir;
use function setcookie;
use function unlink;

class AuthenticationController extends AbstractController
{
    public function __construct(
        Repository $repository,
        TemplateRenderer $templateRenderer,
        private readonly Paths $paths,
        private readonly Config $config,
    ) {
        parent::__construct($repository, $templateRenderer);
    }

    public function login(ServerRequestInterface $request): void
    {
    }

    public function logout(ServerRequestInterface $request): void
    {
        $sessionIdentifier = $request->getAttribute('session');
        if ($sessionIdentifier) {
            $sessionFile = concat_paths(getenv('VXVR_BS_ROOT'), $this->paths->database, 'auth', $sessionIdentifier);
            if (file_exists($sessionFile)) {
                unlink($sessionFile);
            }
            setcookie($this->config->cookieAuthName ?: 'bluesprints_auth', '', -1, '/');
        }
        $this->redirect('/');
    }

    public function authenticate(ServerRequestInterface $request): void
    {
        $this->renderTemplate = false;
        $body = $request->getParsedBody();
        if (array_key_exists('username', $body) && array_key_exists('password', $body)) {
            $username = $body['username'];
            $password = $body['password'];
            if ($username === 'admin' && $password === 'password') {
                $path = concat_paths(getenv('VXVR_BS_ROOT'), $this->paths->database, 'auth');
                $sessionIdentifier = Strings::generateUuid();
                $session = [
                    'id' => $sessionIdentifier,
                    'username' => $username,
                    'authenticated' => true,
                ];
                if (!mkdir($path, $this->config->folderPermissions, true) && !is_dir($path)) {
                    throw new RuntimeException(sprintf('Directory "%s" was not created', $path));
                }
                file_put_contents(concat_paths($path, $sessionIdentifier), json_encode($session));
                setcookie($this->config->cookieAuthName ?: 'bluesprints_auth', $sessionIdentifier);
                $this->redirect('/');
            }
        }
        $this->redirect('/login');
    }
}
