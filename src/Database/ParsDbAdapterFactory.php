<?php


namespace Pars\Core\Database;


use Laminas\Db\Adapter\AdapterInterface;
use Psr\Container\ContainerInterface;

class ParsDbAdapterFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new ParsDbAdapter($container->get(AdapterInterface::class));
    }

}
