<?php

namespace Pars\Core\Translation;

use Laminas\I18n\Translator\TranslatorInterface;
use Pars\Core\Config\ParsConfig;
use Pars\Core\Localization\LocaleFinderInterface;
use Pars\Core\Localization\LocaleInterface;
use Psr\Container\ContainerInterface;

/**
 * Class ParsTranslatorFactory
 * @package Pars\Core\Translation
 */
class ParsTranslatorFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        $saver = $container->get(MissingTranslationSaverInterface::class);
        $namespace = ParsTranslator::NAMESPACE_DEFAULT;
        if (isset($config['translator']['namespace'])) {
            $namespace = $config['translator']['namespace'];
        }
        return new ParsTranslator(
            $container->get(TranslatorInterface::class),
            $container->get(LocaleInterface::class),
            $namespace,
            $saver,
            $container->get(ParsConfig::class),
            $container->get(LocaleFinderInterface::class)
        );
    }

}
