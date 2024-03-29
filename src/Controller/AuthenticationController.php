<?php

declare(strict_types=1);

namespace VerteXVaaR\BlueAuth\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Twig\Environment as View;
use VerteXVaaR\BlueAuth\Service\AuthenticationService;
use VerteXVaaR\BlueSprints\Mvcr\Repository\Repository;
use VerteXVaaR\BlueWeb\Controller\AbstractController;
use VerteXVaaR\BlueWeb\Routing\Attributes\Route;

use function array_key_exists;

class AuthenticationController extends AbstractController
{
    public function __construct(
        Repository $repository,
        View $view,
        private readonly AuthenticationService $authenticationService,
    ) {
        parent::__construct($repository, $view);
    }

    #[Route(path: '/login')]
    public function login(ServerRequestInterface $request): ResponseInterface
    {
        $session = $request->getAttribute('session');
        if ($session->isAuthenticated()) {
            return $this->redirect('/');
        }
        return $this->render('@vertexvaar_blueauth/login.html.twig');
    }

    #[Route(path: '/logout')]
    public function logout(ServerRequestInterface $request): ResponseInterface
    {
        $session = $request->getAttribute('session');

        if ($session->isAuthenticated()) {
            $this->authenticationService->logout($session);
        }
        return $this->redirect('/');
    }

    #[Route(path: '/login', method: Route::POST)]
    public function authenticate(ServerRequestInterface $request): ResponseInterface
    {
        $body = $request->getParsedBody();
        if (array_key_exists('username', $body) && array_key_exists('password', $body)) {
            $session = $request->getAttribute('session');
            $this->authenticationService->authorize($session, $body['username'], $body['password']);

            if ($session->isAuthenticated()) {
                return $this->redirect('/');
            }
        }
        return $this->redirect('/login');
    }
}
