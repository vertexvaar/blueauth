<?php

use Symfony\Component\DependencyInjection\ContainerBuilder;
use VerteXVaaR\BlueAuth\DependencyInjection\AuthorizedRouteCollectorCompilerPass;

return static function (ContainerBuilder $container): void {
    $container->addCompilerPass(new AuthorizedRouteCollectorCompilerPass());
};
