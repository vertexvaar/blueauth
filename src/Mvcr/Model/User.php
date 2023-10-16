<?php

declare(strict_types=1);

namespace VerteXVaaR\BlueAuth\Mvcr\Model;

use VerteXVaaR\BlueSprints\Mvcr\Model\Entity;

use function in_array;

class User extends Entity
{
    public string $username;
    public string $hashedPassword;
    /** @var array<string> */
    public array $roles;

    public function hasRoles(array $roles): bool
    {
        if (empty($roles)) {
            return true;
        }
        if (!isset($this->roles)) {
            return false;
        }
        foreach ($roles as $role) {
            if (!in_array($role, $this->roles, true)) {
                return false;
            }
        }
        return true;
    }
}
