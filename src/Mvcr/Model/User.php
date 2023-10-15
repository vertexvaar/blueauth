<?php

declare(strict_types=1);

namespace VerteXVaaR\BlueAuth\Mvcr\Model;

use VerteXVaaR\BlueSprints\Mvcr\Model\Entity;

class User extends Entity
{
    public string $username;
    public string $hashedPassword;
}
