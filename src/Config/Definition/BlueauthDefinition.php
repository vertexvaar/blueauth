<?php

declare(strict_types=1);

namespace VerteXVaaR\BlueAuth\Config\Definition;

use VerteXVaaR\BlueConfig\Definition\Definition;
use VerteXVaaR\BlueConfig\Structure\Node;
use VerteXVaaR\BlueConfig\Structure\ObjectNode;
use VerteXVaaR\BlueConfig\Structure\StringNode;

class BlueauthDefinition implements Definition
{
    public function get(): Node
    {
        return new ObjectNode(
            'auth',
            'Authentication settings',
            'Set all authentication related settings here',
            [
                new StringNode(
                    'cookieAuthName',
                    'Authentication Cookie Names',
                    'Name for the authentication cookie that bluesprints sets',
                    'auth',
                ),
            ],
        );
    }
}
