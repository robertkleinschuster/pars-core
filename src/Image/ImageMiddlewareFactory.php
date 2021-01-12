<?php


namespace Pars\Core\Image;


use Laminas\Db\Adapter\AdapterInterface;
use Psr\Container\ContainerInterface;

class ImageMiddlewareFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new ImageMiddleware($container->get('config')['image'], $container->get(AdapterInterface::class));
    }

}
