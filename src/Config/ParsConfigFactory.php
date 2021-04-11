<?php


namespace Pars\Core\Config;


use Psr\Container\ContainerInterface;

class ParsConfigFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $appConfig =  $container->get(ParsApplicationConfig::class);
        $config = new ParsConfig(
            $container->get(ConfigFinderInterface::class),
            $appConfig
        );
        if ($appConfig->has('config')) {
            $parsConfig = $appConfig->get('config');
            if (isset($parsConfig['type'])) {
                $config->setType($parsConfig['type']);
            }
        }
        return $config;
    }

}
