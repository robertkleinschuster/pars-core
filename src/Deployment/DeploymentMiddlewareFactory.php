<?php


namespace Pars\Core\Deployment;


use Psr\Container\ContainerInterface;

class DeploymentMiddlewareFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new DeploymentMiddleware($container);
    }

}
