<?php


namespace Pars\Core\Deployment;

use Pars\Core\Container\ParsContainer;
use Psr\Container\ContainerInterface;

/**
 * Class UpdateMiddlewareFactory
 * @package Pars\Core\Deployment
 */
class UpdateMiddlewareFactory
{
    public function __invoke(ContainerInterface $container) {
        return new UpdateMiddleware($container->get(ParsContainer::class), $container->get(UpdaterInterface::class));
    }
}
