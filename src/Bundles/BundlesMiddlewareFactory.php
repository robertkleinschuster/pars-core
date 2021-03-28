<?php

namespace Pars\Core\Bundles;

use Psr\Container\ContainerInterface;

/**
 * Class BundlesMiddlewareFactory
 * @package Pars\Core\Bundles
 */
class BundlesMiddlewareFactory
{
    /**
     * @param ContainerInterface $container
     * @return BundlesMiddleware
     */
    public function __invoke(ContainerInterface $container)
    {
        return new BundlesMiddleware($container);
    }
}
