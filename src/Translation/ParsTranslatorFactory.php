<?php

namespace Pars\Core\Translation;

use Laminas\I18n\Translator\Loader\RemoteLoaderInterface;
use Laminas\I18n\Translator\Translator;
use Laminas\I18n\Translator\TranslatorInterface;
use Pars\Core\Config\ParsConfig;
use Pars\Core\Localization\LocaleFinderInterface;
use Pars\Core\Localization\LocaleInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class ParsTranslatorFactory
 * @package Pars\Core\Translation
 */
class ParsTranslatorFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new ParsTranslator(
            $container->get(TranslatorInterface::class),
            $container->get(LocaleInterface::class),
            $container->get(MissingTranslationSaverInterface::class),
            $container->get(ParsConfig::class),
            $container->get(LocaleFinderInterface::class),
            $container->get(LoggerInterface::class)
        );
    }

}
