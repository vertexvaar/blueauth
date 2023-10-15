<?php

declare(strict_types=1);

namespace VerteXVaaR\BlueAuth\Mvcr\Model;

use VerteXVaaR\BlueSprints\Mvcr\Model\Entity;

class Session extends Entity
{
    private ?string $username = null;

    public function isAuthenticated(): bool
    {
        return null !== $this->username;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function authenticate(string $username): void
    {
        $this->username = $username;
    }

    public function unauthenticate(): void
    {
        $this->username = null;
    }
}
