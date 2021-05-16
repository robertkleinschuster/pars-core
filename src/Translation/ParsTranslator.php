<?php


namespace Pars\Core\Translation;


use Laminas\I18n\Translator\TranslatorAwareInterface;
use Laminas\I18n\Translator\TranslatorAwareTrait;
use Laminas\I18n\Translator\TranslatorInterface;
use Pars\Bean\Type\Base\BeanException;
use Pars\Core\Config\ParsConfig;
use Pars\Core\Localization\LocaleAwareInterface;
use Pars\Core\Localization\LocaleFinderInterface;
use Pars\Core\Localization\LocaleInterface;
use Pars\Core\Translation\Provider\Libretranslate\LibretranslateTranslationProvider;
use Pars\Helper\Placeholder\PlaceholderHelper;

class ParsTranslator implements TranslatorAwareInterface, LocaleAwareInterface
{
    use TranslatorAwareTrait;

    public const NAMESPACE_DEFAULT = 'default';
    public const NAMESPACE_ADMIN = 'admin';
    public const NAMESPACE_FRONTEND = 'frontend';
    public const NAMESPACE_VALIDATION = 'validation';

    protected string $namespace;
    protected LocaleInterface $locale;
    protected MissingTranslationSaverInterface $saver;
    protected ParsConfig $config;
    protected LocaleFinderInterface $localeFinder;

    /**
     * ParsTranslate constructor.
     * @param string $namespace
     * @param LocaleInterface $locale
     */
    public function __construct(
        TranslatorInterface $translator,
        LocaleInterface $locale,
        string $namespace,
        MissingTranslationSaverInterface $saver,
        ParsConfig $config,
        LocaleFinderInterface $localeFinder
    )
    {
        $translator->setLocale($locale->getLocale_Code());
        $this->setTranslator($translator);
        $this->namespace = $namespace;
        $this->locale = $locale;
        $this->saver = $saver;
        $this->config = $config;
        $this->localeFinder = $localeFinder;
    }

    protected function getNamespaceList()
    {
        return [
            self::NAMESPACE_DEFAULT,
            self::NAMESPACE_ADMIN,
            self::NAMESPACE_FRONTEND,
            self::NAMESPACE_VALIDATION,
        ];
    }

    /**
     * @param string $namespace
     * @return $this
     */
    public function setNamespace(string $namespace): self
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @return LocaleInterface
     */
    public function getLocale(): LocaleInterface
    {
        return $this->locale;
    }

    /***
     * @return bool
     */
    public function hasLocale(): bool
    {
        return isset($this->locale);
    }

    /**
     * @param LocaleInterface $locale
     * @return $this
     */
    public function setLocale(LocaleInterface $locale): self
    {
        $this->locale = $locale;
        $this->getTranslator()->setLocale($locale->getLocale_Code());
        return $this;
    }

    /**
     * @param string $text
     * @param array $vars
     * @return string
     * @throws BeanException
     */
    protected function replacePlaceholder(string $text, array $vars = [])
    {
        $result = $text;
        if (count($vars)) {
            $placeholder = new PlaceholderHelper();
            $bean = ParsTranslatorPlaceholderBean::createFromArray($vars);
            $result = $placeholder->replacePlaceholder($text, $bean);
        }
        return $result;
    }

    /**
     * @param string $code
     * @param array $vars
     * @param string|null $namespace
     * @return string
     * @throws BeanException
     */
    public function translate(string $code, array $vars = [], ?string $namespace = null): string
    {
        $code = strtolower(trim($code));
        $namespace = strtolower(trim($namespace));
        $restoreNamespace = null;
        if ($namespace) {
            $restoreNamespace = $this->getNamespace();
            $this->setNamespace($namespace);
        }
        $result = $this->replacePlaceholder($this->getTranslator()->translate($code, $this->getNamespace()), $vars);
        if ($restoreNamespace) {
            $this->setNamespace($restoreNamespace);
        }
        return $result;
    }

    /**
     * @param string $code
     * @param int $count
     * @param array $vars
     * @param string|null $namespace
     * @return string
     * @throws BeanException
     */
    public function translatepl(string $code, int $count, array $vars = [], ?string $namespace = null): string
    {
        $code = strtolower(trim($code));
        $namespace = strtolower(trim($namespace));
        $restoreNamespace = null;
        if ($namespace) {
            $restoreNamespace = $this->getNamespace();
            $this->setNamespace($namespace);
        }
        $result = $this->replacePlaceholder(
            $this->getTranslator()->translatePlural($code, $code, $count, $this->getNamespace()),
            $vars
        );
        if ($restoreNamespace) {
            $this->setNamespace($restoreNamespace);
        }
        return $result;
    }

    /**
     * @param string $locale
     * @param string $code
     * @param string $namespace
     * @return $this
     */
    public function saveMissingTranslation(string $locale, string $code, string $namespace)
    {
        register_shutdown_function(function () use ($locale, $code, $namespace) {
            try {
                $text = $code;
                $default = $this->localeFinder->findLocale($this->config->get('locale.default'), null, null);
                $target = $this->localeFinder->findLocale($locale, null, null);
                if ($default->getLocale_Code() != $target->getLocale_Code()) {
                    $sourceText = $this->getTranslator()->translate($code, $this->getNamespace(), $default->getLocale_Code());
                    if ($default->getLocale_Language() == $target->getLocale_Language()) {
                        $text = $sourceText;
                    } else {
                        $targetText = $this->autotranslate($sourceText, $default, $target);
                        if ($sourceText !== $targetText) {
                            $text = $targetText;
                        } else {
                            $text = $sourceText;
                        }
                    }
                }
                $this->saver->saveMissingTranslation($locale, $code, $namespace, $text);
            } catch (\Throwable $exception) {
                $this->saver->saveMissingTranslation($locale, $code, $namespace);
            }
           });
        return $this;
    }

    public function clearCache()
    {
        try {
            $this->clearTranslationsSource($this->localeFinder->findActiveLocaleCodeList());;
        } catch (\Throwable $exception) {
        }
    }

    protected function clearTranslationsTextDomain(string $textDomain, array $localeList)
    {
        foreach ($localeList as $locale) {
            $this->getTranslator()->clearCache(
                $textDomain,
                $locale
            );
        }
    }

    protected function clearTranslationsSource(array $localeList)
    {
        foreach ($this->getNamespaceList() as $namespace) {
            $this->clearTranslationsTextDomain($namespace, $localeList);
        }
    }


    /**
     * @param string $text
     * @param LocaleInterface $from
     * @param LocaleInterface $to
     * @return string
     */
    public function autotranslate(string $text, LocaleInterface $from, LocaleInterface $to): string
    {
        $provider = new LibretranslateTranslationProvider($this->config);
        return $provider->translate($text, $from, $to);
    }
}
