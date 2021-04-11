<?php


namespace Pars\Core\Config;


use Psr\Container\ContainerInterface;

class ParsApplicationConfigFactory
{
    /**
     * @param ContainerInterface $container
     * @return mixed
     */
    public function __invoke(ContainerInterface $container)
    {
        return new ParsApplicationConfig($container->get('config'));
    }

}
