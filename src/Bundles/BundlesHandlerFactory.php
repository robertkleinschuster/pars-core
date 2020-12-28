<?php


namespace Pars\Core\Bundles;


use Pars\Core\Bundles\BundlesHandler;
use Psr\Container\ContainerInterface;

class BundlesHandlerFactory
{
    /**
     * @param ContainerInterface $container
     * @return BundlesHandler
     */
    public function __invoke(ContainerInterface $container)
    {
        return new BundlesHandler($container->get('config')['bundles']);
    }
}
