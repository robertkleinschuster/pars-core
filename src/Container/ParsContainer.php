<?php

namespace Pars\Core\Container;


use Mezzio\Helper\UrlHelper;
use Pars\Core\Config\ParsConfig;
use Pars\Core\Database\ParsDatabaseAdapter;
use Pars\Core\Database\ParsDatabaseAdapterAwareInterface;
use Pars\Core\Database\ParsDatabaseAdapterAwareTrait;
use Pars\Core\Image\ImageProcessor;
use Pars\Core\Localization\LocaleInterface;
use Pars\Core\Translation\ParsTranslator;
use Pars\Core\Translation\ParsTranslatorAwareInterface;
use Pars\Core\Translation\ParsTranslatorAwareTrait;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

/**
 * Class ParsContainer
 * @package Pars\Core\Container
 */
class ParsContainer implements ParsTranslatorAwareInterface, ParsDatabaseAdapterAwareInterface, LoggerAwareInterface
{
    use ParsTranslatorAwareTrait;
    use ParsDatabaseAdapterAwareTrait;
    use LoggerAwareTrait;
    protected ContainerInterface $container;
    protected LocaleInterface $locale;
    protected ParsConfig $config;
    protected ImageProcessor $imageProcessor;
    protected UrlHelper $urlHelper;

    /**
     * ParsContainer constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return ParsTranslator
     */
    public function getTranslator(): ParsTranslator
    {
        if (!$this->hasTranslator()) {
            $this->setTranslator($this->container->get(ParsTranslator::class));
        }
        return $this->translator;
    }

    /**
     * @return ParsDatabaseAdapter
     */
    public function getDatabaseAdapter(): ParsDatabaseAdapter
    {
        if (!$this->hasDatabaseAdapter()) {
            $this->setDatabaseAdapter($this->container->get(ParsDatabaseAdapter::class));
        }
        return $this->databaseAdapter;
    }


    /**
     * @return LocaleInterface
     */
    public function getLocale(): LocaleInterface
    {
        if (!isset($this->locale)) {
            $this->locale = $this->container->get(LocaleInterface::class);
        }
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
        if (!isset($this->logger)) {
            $this->logger = $this->container->get(LoggerInterface::class);
        }
        return $this->logger;
    }

    /**
     * @return ParsConfig
     */
    public function getConfig(): ParsConfig
    {
        if (!isset($this->config)) {
            $this->config = $this->container->get(ParsConfig::class);
        }
        return $this->config;
    }

    /**
     * @param ParsConfig $config
     */
    public function setConfig(ParsConfig $config): void
    {
        $this->config = $config;
    }

    /**
     * @return ImageProcessor
     */
    public function getImageProcessor(): ImageProcessor
    {
        if (!isset($this->imageProcessor)) {
            $this->imageProcessor = $this->container->get(ImageProcessor::class);
        }
        return $this->imageProcessor;
    }

    /**
     * @param ImageProcessor $imageProcessor
     */
    public function setImageProcessor(ImageProcessor $imageProcessor): void
    {
        $this->imageProcessor = $imageProcessor;
    }

    /**
     * @return UrlHelper
     */
    public function getUrlHelper(): UrlHelper
    {
        if (!isset($this->urlHelper)) {
            $this->urlHelper = $this->container->get(UrlHelper::class);
        }
        return $this->urlHelper;
    }

    /**
     * @param UrlHelper $urlHelper
     */
    public function setUrlHelper(UrlHelper $urlHelper): void
    {
        $this->urlHelper = $urlHelper;
    }



}
