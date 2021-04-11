<?php

namespace Pars\Core\Translation;

use Pars\Core\Config\ParsConfig;
use Psr\Container\ContainerInterface;

/**
 * Class TranslatorMiddlewareFactory
 * @package Pars\Core\Translation
 */
class TranslatorMiddlewareFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new TranslatorMiddleware($container->get(ParsTranslator::class), $container->get(ParsConfig::class));
    }
}
