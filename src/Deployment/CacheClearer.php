<?php

namespace Pars\Core\Deployment;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\RequestOptions;
use Pars\Core\Cache\ParsCache;
use Pars\Core\Cache\ParsMultiCache;
use Pars\Core\Config\ParsConfig;
use Pars\Core\Container\ParsContainer;
use Pars\Core\Container\ParsContainerAwareTrait;
use Pars\Core\Translation\ParsTranslator;
use Pars\Helper\Filesystem\FilesystemHelper;
use Pars\Pattern\Option\OptionAwareInterface;
use Pars\Pattern\Option\OptionAwareTrait;

/**
 * Class Cache
 * @package Pars\Core\Deployment
 */
class CacheClearer implements OptionAwareInterface
{
    public const OPTION_RESET_OPCACHE = 'reset_opcache';
    public const OPTION_CLEAR_CONFIG = 'clear_config';
    public const OPTION_CLEAR_BUNDLES = 'clear_bundles';
    public const OPTION_CLEAR_ASSETS = 'clear_assets';
    public const OPTION_CLEAR_CACHE_POOL = 'clear_cache_pool';
    public const OPTION_CLEAR_IMAGES = 'clear_images';
    public const OPTION_CLEAR_TRANSLATIONS = 'clear_translations';
    public const OPTION_CLEAR_TEMPLATES = 'clear_templates';

    use OptionAwareTrait;
    use ParsContainerAwareTrait;

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
     * @param ParsContainer $config
     */
    public function __construct(ParsContainer $parsContainer)
    {
        $this->setParsContainer($parsContainer);
        $this->config = $parsContainer->getConfig();
        $this->translator = $parsContainer->getTranslator();
        $this->addOption(self::OPTION_CLEAR_CACHE_POOL);
        $this->addOption(self::OPTION_RESET_OPCACHE);
        $this->addOption(self::OPTION_CLEAR_CONFIG);
        $this->addOption(self::OPTION_CLEAR_TEMPLATES);
    }

    /**
     * @param array $config
     */
    public static function registerShutdownErrorFunction(array $config)
    {
        register_shutdown_function(function () use ($config) {
            $error = error_get_last();
            if (isset($error['type']) && $error['type'] == E_ERROR) {
                self::clearConfigCache($config);
                throw new \Exception('Clear config cache: ' . implode($error));
            }
        });
    }

    /**
     * @param array $config
     */
    public static function clearConfigCache(array $config)
    {
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
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

    /**
     * @return ParsConfig
     */
    public function getConfig(): ParsConfig
    {
        return $this->config;
    }


    public function clear()
    {
        try {
            $this->getParsContainer()->getLogger()->info('CLEAR SELF');
            clearstatcache(true);
            if (
                function_exists('opcache_reset')
                && $this->hasOption(self::OPTION_RESET_OPCACHE)
            ) {
                opcache_reset();
                FilesystemHelper::deleteDirectory(PARS_OPCACHE_DIR);
                if (!is_dir(PARS_OPCACHE_DIR)) {
                    mkdir(PARS_OPCACHE_DIR);
                }
            }
            if ($this->hasOption(self::OPTION_CLEAR_CONFIG)) {
                $this->clearConfig();
            }
            if ($this->hasOption(self::OPTION_CLEAR_CACHE_POOL)) {
                $this->clearPool();
            }
            if ($this->hasOption(self::OPTION_CLEAR_TEMPLATES)) {
                $this->clearTemplates();
            }
        } catch (\Throwable $exception) {
            $this->getParsContainer()->getLogger()->error('CLEAR ERROR', ['exception' => $exception]);
        }

    }

    protected function clearTemplates()
    {
        try {
            FilesystemHelper::deleteDirectory('data/cache/twig');
        } catch (\Throwable $exception) {
            $this->getParsContainer()->getLogger()->error('CLEAR ERROR', ['exception' => $exception]);
        }
    }

    public function clearRemote()
    {
        $try = 0;
        while ($try < 5 && $this->clearFrontend() === false) {
            $try++;
        }
        $try = 0;
        while ($try < 5 && $this->clearAdmin() === false) {
            $try++;
        }
    }

    protected function clearFrontend()
    {
        $domain = $this->config->getFrontendDomain();
        return $this->clearByDomain($domain);
    }

    protected function clearAdmin()
    {
        $domain = $this->config->getAssetDomain();
        return $this->clearByDomain($domain);
    }


    protected function clearByDomain(string $domain)
    {
        $domainUri = new Uri($domain);
        $domainUri = Uri::withQueryValue($domainUri, 'nopropagate', true);
        $domainUri = Uri::withQueryValue($domainUri, 'clearcache', $this->getConfig()->getSecret(true));
        try {
            $client = new Client();
            $this->getParsContainer()->getLogger()->info('CLEAR: ' . $domainUri);
            $response = $client->get($domainUri, [
                RequestOptions::TIMEOUT => 5,
                RequestOptions::CONNECT_TIMEOUT => 5,
                RequestOptions::READ_TIMEOUT => 5,
            ]);
            if ($response->getStatusCode() == 200 && $response->hasHeader('clear-success')) {
                $this->getParsContainer()->getLogger()->info('CLEAR SUCCESS: ' . $domainUri);
                return true;
            } else {
                $this->getParsContainer()->getLogger()->info('CLEAR ERROR: ' . $domainUri);
            }
        } catch (\Throwable $exception) {
            $this->getParsContainer()->getLogger()->error('CLEAR ERROR', ['exception' => $exception]);
        }
        return false;
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
        ParsCache::clearAll();
        ParsMultiCache::clearAll();
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
