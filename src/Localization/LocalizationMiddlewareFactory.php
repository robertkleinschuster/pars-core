<?php

namespace Pars\Core\Localization;

use Mezzio\Helper\UrlHelper;
use Pars\Core\Config\ParsConfig;
use Psr\Container\ContainerInterface;

/**
 * Class LocalizationMiddlewareFactory
 * @package Pars\Core\Localization
 */
class LocalizationMiddlewareFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new LocalizationMiddleware(
            $container->get(UrlHelper::class),
            $container->get(ParsConfig::class),
            $container->get(LocaleFinderInterface::class),
        );
    }
}
