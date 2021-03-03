<?php

namespace Pars\Core\Bundles;

use Psr\Container\ContainerInterface;

class BundlesMiddlewareFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new BundlesMiddleware($container->get('config')['bundles']);
    }
}
