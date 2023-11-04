<?php

declare(strict_types=1);

namespace VerteXVaaR\BlueAuth\DependencyInjection;

use Composer\IO\IOInterface;
use OutOfBoundsException;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use VerteXVaaR\BlueAuth\Routing\Attributes\AuthorizedRoute;
use VerteXVaaR\BlueWeb\Routing\Middleware\RoutingMiddleware;

use function array_keys;
use function array_merge_recursive;
use function array_replace;
use function krsort;
use function sprintf;

class AuthorizedRouteCollectorCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        /** @var IOInterface $io */
        $io = $container->get('io');
        $io->write('Loading authorized routes from controller attributes', true, IOInterface::VERBOSE);

        $compiledRoutes = [];
        $controllers = $container->findTaggedServiceIds('vertexvaar.bluesprints.controller');
        foreach (array_keys($controllers) as $controller) {
            $definition = $container->getDefinition($controller);
            $definition->setPublic(true);
            $controllerClass = $definition->getClass();
            try {
                $reflection = new ReflectionClass($controllerClass);
            } catch (ReflectionException $exception) {
                $io->writeError(
                    sprintf(
                        'Could not reflect controller "%s". Exception: %s',
                        $controllerClass,
                        $exception->getMessage(),
                    ),
                );
                continue;
            }
            $reflectionMethods = $reflection->getMethods();
            if (empty($reflectionMethods)) {
                $io->write(
                    sprintf('Controller "%s" does not define any methods', $controllerClass),
                    true,
                    IOInterface::VERBOSE,
                );
                continue;
            }
            foreach ($reflectionMethods as $reflectionMethod) {
                $attributes = $reflectionMethod->getAttributes(AuthorizedRoute::class);
                foreach ($attributes as $attribute) {
                    /** @var AuthorizedRoute $authorizedRoute */
                    $authorizedRoute = $attribute->newInstance();
                    $methodName = $reflectionMethod->getName();
                    $io->write(
                        sprintf(
                            'Found route [%d][%s] "%s" in controller "%s" method "%s"',
                            $authorizedRoute->priority,
                            $authorizedRoute->method,
                            $authorizedRoute->path,
                            $controllerClass,
                            $methodName,
                        ),
                        true,
                        IOInterface::VERBOSE,
                    );
                    $compiledRoutes[$authorizedRoute->method][$authorizedRoute->priority][$authorizedRoute->path] = [
                        'class' => \VerteXVaaR\BlueAuth\Routing\AuthorizedRoute::class,
                        'controller' => $controllerClass,
                        'action' => $methodName,
                        'requireAuthorization' => $authorizedRoute->requireAuthorization || !empty($authorizedRoute->requiredRoles),
                        'requiredRoles' => $authorizedRoute->requiredRoles,
                    ];
                }
            }
        }

        foreach ($compiledRoutes as $method => $paths) {
            krsort($paths);
            $compiledRoutes[$method] = array_replace([], ...$paths);
        }

        $definition = $container->getDefinition(RoutingMiddleware::class);
        try {
            $routes = $definition->getArgument('$routes');
        } catch (OutOfBoundsException $exception) {
            $routes = [];
        }
        $routes = array_merge_recursive($routes, $compiledRoutes);
        $definition->setArgument('$routes', $routes);

        $io->write('Loaded authorized routes from controller attributes', true, IOInterface::VERBOSE);
    }
}
