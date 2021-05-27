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
        $parsContainer = new ParsContainer();
        $parsContainer->setLocale($container->get(LocaleInterface::class));
        $parsContainer->setTranslator($container->get(ParsTranslator::class));
        $parsContainer->setDatabaseAdapter($container->get(ParsDatabaseAdapter::class));
        $parsContainer->setLogger($container->get(LoggerInterface::class));
        $parsContainer->setConfig($container->get(ParsConfig::class));
        $parsContainer->setImageProcessor($container->get(ImageProcessor::class));
        $parsContainer->setUrlHelper($container->get(UrlHelper::class));
        return $parsContainer;
    }
}
