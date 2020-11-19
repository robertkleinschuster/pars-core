<?php

namespace Pars\Core\Localization;

use Mezzio\Helper\UrlHelper;
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
            $container->get('config')['localization'],
            $container->get(LocaleFinderInterface::class)
        );
    }
}
