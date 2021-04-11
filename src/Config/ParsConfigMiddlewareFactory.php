<?php


namespace Pars\Core\Config;


use Psr\Container\ContainerInterface;

class ParsConfigMiddlewareFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new ParsConfigMiddleware($container->get(ParsConfig::class));
    }

}
