<?php

declare(strict_types=1);

namespace VerteXVaaR\BlueAuth\Service;

use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use SensitiveParameter;
use VerteXVaaR\BlueAuth\Mvcr\Model\Session;
use VerteXVaaR\BlueAuth\Mvcr\Model\User;
use VerteXVaaR\BlueSprints\Environment\Config;
use VerteXVaaR\BlueSprints\Mvcr\Repository\Repository;

use function in_array;
use function password_verify;

readonly class AuthenticationService
{
    public function __construct(
        private Repository $repository,
        private Config $config,
    ) {
    }

    public function authorize(
        Session $session,
        string $username,
        #[SensitiveParameter] string $password
    ): void {
        // If there is a session for another user, delete the session first.
        if (!in_array($session->getUsername(), [null, $username], true)) {
            $this->logout($session);
            $session = new Session(Uuid::uuid4()->toString());
        }
        $user = $this->repository->findByIdentifier(User::class, $username);
        if (null === $user) {
            // Destroy session if user does not exist (anymore)
            $this->logout($session);
            return;
        }
        if (password_verify($password, $user->hashedPassword)) {
            $session->authenticate($username);
            setcookie($this->config->cookieAuthName, $session->identifier);
            $this->repository->persist($session);
        }
    }

    public function logout(Session $session): void
    {
        $session->unauthenticate();
        setcookie($this->config->cookieAuthName, '', -1, '/');
        $this->repository->delete($session);
    }

    public function loadSessionFromRequest(ServerRequestInterface $request): Session
    {
        $sessionIdentifier = $request->getCookieParams()[$this->config->cookieAuthName] ?? null;
        if (null === $sessionIdentifier) {
            return new Session(Uuid::uuid4()->toString());
        }

        $session = $this->repository->findByIdentifier(Session::class, $sessionIdentifier);
        if (null === $session || null === $session->getUsername()) {
            return new Session(Uuid::uuid4()->toString());
        }

        $user = $this->repository->findByIdentifier(User::class, $session->getUsername());
        if (null === $user) {
            // Destroy session if user does not exist (anymore)
            $this->logout($session);
            // Create new session with new identifier
            return new Session(Uuid::uuid4()->toString());
        }

        return $session;
    }
}