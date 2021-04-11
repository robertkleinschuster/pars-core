<?php


namespace Pars\Core\Deployment;


use Laminas\Db\Adapter\AdapterInterface;
use Pars\Core\Config\ParsConfig;
use Pars\Core\Translation\ParsTranslator;
use Psr\Container\ContainerInterface;

class CacheClearerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new CacheClearer($container->get(ParsConfig::class), $container->get(AdapterInterface::class), $container->get(ParsTranslator::class));
    }

}
