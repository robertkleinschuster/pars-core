<?php


namespace Pars\Core\Deployment;


use Psr\Container\ContainerInterface;

class ParsUpdaterFactory
{
    /**
     * @param ContainerInterface $container
     * @return UpdaterInterface
     */
    public function __invoke(ContainerInterface $container): UpdaterInterface
    {
        return new ParsUpdater($container);
    }
}
