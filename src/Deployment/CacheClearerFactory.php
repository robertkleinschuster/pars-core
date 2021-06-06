<?php


namespace Pars\Core\Deployment;



use Pars\Core\Container\ParsContainer;
use Psr\Container\ContainerInterface;

class CacheClearerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new CacheClearer($container->get(ParsContainer::class));
    }

}
