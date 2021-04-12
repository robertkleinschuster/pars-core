<?php


namespace Pars\Core\Database;


use Laminas\Db\Adapter\AdapterInterface;
use Psr\Container\ContainerInterface;

class ParsDatabaseAdapterFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new ParsDatabaseAdapter($container->get(AdapterInterface::class));
    }

}
