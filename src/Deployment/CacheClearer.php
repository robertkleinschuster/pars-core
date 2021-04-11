<?php

namespace Pars\Core\Deployment;

use Laminas\Db\Adapter\AdapterAwareInterface;
use Laminas\Db\Adapter\AdapterAwareTrait;
use Laminas\Db\Adapter\AdapterInterface;
use Pars\Core\Config\ParsConfig;
use Pars\Core\Translation\ParsTranslator;
use Pars\Pattern\Option\OptionAwareInterface;
use Pars\Pattern\Option\OptionAwareTrait;
use Pars\Core\Cache\ParsCache;
use Pars\Helper\Filesystem\FilesystemHelper;

/**
 * Class Cache
 * @package Pars\Core\Deployment
 */
class CacheClearer implements AdapterAwareInterface, OptionAwareInterface
{
    public const OPTION_RESET_OPCACHE = 'reset_opcache';
    public const OPTION_CLEAR_CONFIG = 'clear_config';
    public const OPTION_CLEAR_BUNDLES = 'clear_bundles';
    public const OPTION_CLEAR_ASSETS = 'clear_assets';
    public const OPTION_CLEAR_CACHE_POOL = 'clear_cache_pool';
    public const OPTION_CLEAR_IMAGES = 'clear_images';
    public const OPTION_CLEAR_TRANSLATIONS = 'clear_translations';

    use OptionAwareTrait;
    use AdapterAwareTrait;

    /**
     * @var ParsConfig
     */
    protected ParsConfig $config;

    /**
     * @var ParsTranslator
     */
    protected ParsTranslator $translator;

    /**
     * Cache constructor.
     * @param ParsConfig $config
     */
    public function __construct(ParsConfig $config, AdapterInterface $adapter, ParsTranslator $translator)
    {
        $this->config = $config;
        $this->translator = $translator;
        $this->setDbAdapter($adapter);
        $this->addOption(self::OPTION_CLEAR_ASSETS);
        $this->addOption(self::OPTION_CLEAR_BUNDLES);
        $this->addOption(self::OPTION_CLEAR_CACHE_POOL);
        $this->addOption(self::OPTION_RESET_OPCACHE);
        $this->addOption(self::OPTION_CLEAR_IMAGES);
        $this->addOption(self::OPTION_CLEAR_TRANSLATIONS);
        $this->addOption(self::OPTION_CLEAR_CONFIG);
    }

    /**
     * @param array $config
     */
    public static function registerConfigCacheFunction(array $config)
    {
        register_shutdown_function(function () use ($config) {
            $error = error_get_last();
            if (isset($error['type']) && $error['type'] === E_ERROR) {
                if (isset($config['config_cache_path']) && file_exists($config['config_cache_path'])) {
                    unlink($config['config_cache_path']);
                }
                if (file_exists('data/cache/config/config.php')) {
                    unlink('data/cache/config/config.php');
                }
                if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/../data/cache/config/config.php')) {
                    unlink($_SERVER['DOCUMENT_ROOT'] . '/../data/cache/config/config.php');
                }
            }
        });
    }

    /**
     * @return ParsConfig
     */
    public function getConfig(): ParsConfig
    {
        return $this->config;
    }


    public function clear()
    {
        if (
            function_exists('opcache_reset')
            && $this->hasOption(self::OPTION_RESET_OPCACHE)
        ) {
            opcache_reset();
        }
        if ($this->hasOption(self::OPTION_CLEAR_CONFIG)) {
            $this->clearConfig();
        }
        if ($this->hasOption(self::OPTION_CLEAR_CACHE_POOL)) {
            $this->clearPool();
        }
        $this->clearSession();
        if ($this->hasOption(self::OPTION_CLEAR_BUNDLES)) {
            $this->clearBundles();
        }
        if ($this->hasOption(self::OPTION_CLEAR_ASSETS)) {
            $this->clearAssets();
        }
        if ($this->hasOption(self::OPTION_CLEAR_IMAGES)) {
            $this->clearImages();
        }
        if ($this->hasOption(self::OPTION_CLEAR_TRANSLATIONS)) {
            $this->clearTranslations();
        }
    }

    protected function getAppConfig(string $key)
    {
        return $this->getConfig()->getFromAppConfig($key);
    }

    protected function clearConfig()
    {
        if (file_exists($this->getAppConfig('config_cache_path'))) {
            unlink($this->getConfig()->getFromAppConfig('config_cache_path'));
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
        $sessionConfig = $this->getAppConfig('mezzio-session-cache');
        if (is_dir($sessionConfig['filesystem_folder'])) {
            $dir = $sessionConfig['filesystem_folder'];
            $files = array_diff(scandir($dir), ['.', '..']);
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
        $bundlesConfig = $this->getAppConfig('bundles');
        if (isset($bundlesConfig['list'])) {
            foreach ($bundlesConfig['list'] as $item) {
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
        $assetConfig = $this->getAppConfig('assets');
        if (isset($assetConfig['list'])) {
            foreach ($assetConfig['list'] as $item) {
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
        $imageConfig = $this->getAppConfig('image');
        if (isset($imageConfig['cache'])) {
            if (is_dir($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $imageConfig['cache'])) {
                FilesystemHelper::deleteDirectory(
                    $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $imageConfig['cache']
                );
            }
        }
    }


    protected function clearTranslations()
    {
        $this->translator->clearCache();
    }

}
