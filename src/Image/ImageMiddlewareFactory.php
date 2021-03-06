<?php

namespace Pars\Core\Image;


use Psr\Container\ContainerInterface;

class ImageMiddlewareFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new ImageMiddleware($container->get(ImageProcessor::class));
    }
}
