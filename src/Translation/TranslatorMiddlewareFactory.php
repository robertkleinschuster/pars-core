<?php

namespace Pars\Core\Translation;

use Pars\Core\Config\ParsConfig;
use Pars\Core\Container\ParsContainer;
use Psr\Container\ContainerInterface;

/**
 * Class TranslatorMiddlewareFactory
 * @package Pars\Core\Translation
 */
class TranslatorMiddlewareFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new TranslatorMiddleware($container->get(ParsContainer::class));
    }
}
