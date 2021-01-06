<?php


namespace Pars\Core\Assets;


use Psr\Container\ContainerInterface;

class AssetsMiddlewareFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new AssetsMiddleware($container->get('config')['assets']);
    }

}
