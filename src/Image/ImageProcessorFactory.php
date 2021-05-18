<?php


namespace Pars\Core\Image;


use Pars\Core\Container\ParsContainer;
use Psr\Container\ContainerInterface;

class ImageProcessorFactory
{
    public function __invoke(ContainerInterface $container){
        return new ImageProcessor($container->get(ParsContainer::class));
    }
}
