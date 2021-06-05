<?php


namespace Pars\Core\Container;


use Mezzio\Helper\UrlHelper;
use Pars\Core\Config\ParsConfig;
use Pars\Core\Database\ParsDatabaseAdapter;
use Pars\Core\Image\ImageProcessor;
use Pars\Core\Localization\LocaleInterface;
use Pars\Core\Translation\ParsTranslator;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class ParsContainerFactory
{
    /**
     * @param ContainerInterface $container
     * @return ParsContainer
     */
    public function __invoke(ContainerInterface $container)
    {
        return new ParsContainer($container);
    }
}
