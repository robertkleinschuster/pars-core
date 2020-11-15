<?php

namespace Pars\Core\Authentication;

use Psr\Container\ContainerInterface;

/**
 * Class AuthenticationMiddlewareFactory
 * @package Pars\Core\Authentication
 */
class AuthenticationMiddlewareFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new AuthenticationMiddleware($container);
    }
}
