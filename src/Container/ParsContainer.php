<?php

namespace Pars\Core\Container;


use Pars\Core\Config\ParsConfig;
use Pars\Core\Database\ParsDatabaseAdapterAwareInterface;
use Pars\Core\Database\ParsDatabaseAdapterAwareTrait;
use Pars\Core\Localization\LocaleInterface;
use Pars\Core\Translation\ParsTranslatorAwareInterface;
use Pars\Core\Translation\ParsTranslatorAwareTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

class ParsContainer implements ParsTranslatorAwareInterface, ParsDatabaseAdapterAwareInterface, LoggerAwareInterface
{
    use ParsTranslatorAwareTrait;
    use ParsDatabaseAdapterAwareTrait;
    use LoggerAwareTrait;
    protected LocaleInterface $locale;
    protected ParsConfig $config;
    /**
     * @return LocaleInterface
     */
    public function getLocale(): LocaleInterface
    {
        return $this->locale;
    }

    /**
     * @param LocaleInterface $locale
     */
    public function setLocale(LocaleInterface $locale): void
    {
        $this->locale = $locale;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @return ParsConfig
     */
    public function getConfig(): ParsConfig
    {
        return $this->config;
    }

    /**
     * @param ParsConfig $config
     */
    public function setConfig(ParsConfig $config): void
    {
        $this->config = $config;
    }





}
