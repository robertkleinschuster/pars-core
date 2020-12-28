<?php


namespace Pars\Core\Assets;


use Psr\Container\ContainerInterface;

class AssetsHandlerFactory
{
    /**
     * @param ContainerInterface $container
     * @return AssetsHandler
     */
    public function __invoke(ContainerInterface $container)
    {
        return new AssetsHandler($container->get('config')['assets']);
    }
}
