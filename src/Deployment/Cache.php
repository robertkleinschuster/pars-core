<?php


namespace Pars\Core\Deployment;


use Laminas\Db\Adapter\AdapterAwareInterface;
use Laminas\Db\Adapter\AdapterAwareTrait;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\I18n\Translator\TranslatorAwareInterface;
use Laminas\I18n\Translator\TranslatorAwareTrait;
use Pars\Core\Cache\ParsCache;
use Pars\Helper\Filesystem\FilesystemHelper;
use Pars\Model\Localization\Locale\LocaleBeanFinder;
use Pars\Model\Localization\Locale\LocaleBeanList;

class Cache implements AdapterAwareInterface, TranslatorAwareInterface
{
    use AdapterAwareTrait;
    use TranslatorAwareTrait;

    /**
     * @var array
     */
    protected array $applicationConfig;

    /**
     * Cache constructor.
     * @param array $applicationConfig
     */
    public function __construct(array $applicationConfig, AdapterInterface $adapter)
    {
        $this->applicationConfig = $applicationConfig;
        $this->setDbAdapter($adapter);
    }


    public function clear()
    {
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
        $this->clearConfig();
        $this->clearPool();
        $this->clearSession();
        $this->clearBundles();
        $this->clearAssets();
        $this->clearImages();
        $this->clearTranslations();
    }

    protected function clearConfig()
    {
        if (file_exists($this->applicationConfig['config_cache_path'])) {
            unlink($this->applicationConfig['config_cache_path']);
        }
    }


    protected function clearPool()
    {
        if (is_dir(ParsCache::DEFAULT_BASE_PATH)) {
            FilesystemHelper::deleteDirectory(ParsCache::DEFAULT_BASE_PATH);
        }

    }


    protected function clearSession()
    {
        if (is_dir($this->applicationConfig['mezzio-session-cache']['filesystem_folder'])) {
            $dir = $this->applicationConfig['mezzio-session-cache']['filesystem_folder'];
            $files = array_diff(scandir($dir), array('.', '..'));
            foreach ($files as $file) {
                $path = $dir . DIRECTORY_SEPARATOR . $file;
                if (is_file($path) && strpos($path, '.php') !== false) {
                    $data = require $path;
                    if (
                        isset($data[3]) && $data[3] < time()
                        || (time() - filemtime($path) > 3600)
                    ) {
                        unlink($path);
                    }
                }
            }
        }
    }

    protected function clearBundles()
    {
        if (isset($this->applicationConfig['bundles']['list'])) {
            foreach ($this->applicationConfig['bundles']['list'] as $item) {
                if (isset($item['output'])) {
                    $filename = $item['output'];
                    $path = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . FilesystemHelper::injectHash($filename, '*');
                    $files = glob($path);
                    if (is_array($files)) {
                        foreach ($files as $file) {
                            if (file_exists($file)) {
                                unlink($file);
                            }
                        }
                    }
                }
            }
        }
    }

    protected function clearAssets()
    {
        if (isset($this->applicationConfig['assets']['list'])) {
            foreach ($this->applicationConfig['assets']['list'] as $item) {
                if (isset($item['output'])) {
                    if (file_exists($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $item['output'])) {
                        unlink($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $item['output']);
                    }
                }
            }
        }
    }


    protected function clearImages()
    {

        if (isset($this->applicationConfig['image']['cache'])) {
            if (is_dir($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $this->applicationConfig['image']['cache'])) {
                FilesystemHelper::deleteDirectory(
                    $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $this->applicationConfig['image']['cache']
                );
            }

        }
    }


    protected function clearTranslations()
    {
        if ($this->hasTranslator()) {
            $localeList = null;
            $localeFinder = new LocaleBeanFinder($this->adapter);
            $localeFinder->setLocale_Active(true);
            $localeList = $localeFinder->getBeanList();
            $this->clearTranslationsSource('translation_file_patterns', $localeList);
            $this->clearTranslationsSource('translation_files', $localeList);
            $this->clearTranslationsSource('remote_translation', $localeList);
        }
    }

    protected function clearTranslationsTextDomain(string $textDomain, LocaleBeanList $localeList)
    {
        if ($localeList !== null) {
            foreach ($localeList as $locale) {
                $this->getTranslator()->clearCache(
                    $textDomain,
                    $locale->get('Locale_Code')
                );
            }
        } elseif (isset($this->applicationConfig['translator']['locale'])
            && is_array($this->applicationConfig['translator']['locale'])
        ) {
            foreach ($this->applicationConfig['translator']['locale'] as $locale) {
                $this->getTranslator()->clearCache($textDomain, $locale);
            }
        }
    }

    protected function clearTranslationsSource(string $source, LocaleBeanList $localeList)
    {
        if (isset($this->applicationConfig['translator'][$source])
            && is_array($this->applicationConfig['translator'][$source])) {
            foreach ($this->applicationConfig['translator'][$source] as $translation_file_pattern) {
                if (isset($translation_file_pattern['text_domain'])) {
                    $this->clearTranslationsTextDomain($translation_file_pattern['text_domain'], $localeList);
                }
            }
        }

    }
}
