<?php


namespace Pars\Core\Image;


use Pars\Core\Config\ParsConfig;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class ImageProcessorFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new ImageProcessor($container->get(ParsConfig::class), $container->get(LoggerInterface::class));
    }
}
