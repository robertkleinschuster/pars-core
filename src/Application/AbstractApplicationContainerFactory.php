<?php


namespace Pars\Core\Application;

use Laminas\ConfigAggregator\ArrayProvider;
use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\PhpFileProvider;

abstract class AbstractApplicationContainerFactory
{
    /**
     * @var array
     */
    protected array $configProvider = [];

    /**
     * @return AbstractApplicationContainer
     */
    public function __invoke()
    {
        $config = $this->getApplicationConfig();
        $dependencies = $config['dependencies'];
        $dependencies['services']['config'] = $config;
        return $this->createApplicationContainer($dependencies);
    }

    /**
     * @return array
     */
    protected function getApplicationConfig()
    {
        $cachePath = PARS_DIR . '/data/cache/config/config.php';
        $cacheDir = dirname($cachePath);
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir);
        }
        $cacheConfig = ['config_cache_path' => $cachePath];
        $this->configProvider = [
            new ArrayProvider($cacheConfig),
            \Laminas\Mail\ConfigProvider::class,
            \Laminas\Validator\ConfigProvider::class,
            \Laminas\I18n\ConfigProvider::class,
            \Laminas\Log\ConfigProvider::class,
            \Laminas\Db\ConfigProvider::class,
            \Laminas\HttpHandlerRunner\ConfigProvider::class,
            \Laminas\Diactoros\ConfigProvider::class,
            \Mezzio\ConfigProvider::class,
            \Mezzio\Helper\ConfigProvider::class,
            \Mezzio\Flash\ConfigProvider::class,
            \Mezzio\Session\ConfigProvider::class,
            \Mezzio\Session\Cache\ConfigProvider::class,
            \Mezzio\Csrf\ConfigProvider::class,
            \Mezzio\Authentication\ConfigProvider::class,
            \Mezzio\Router\ConfigProvider::class,
            \Mezzio\Router\FastRouteRouter\ConfigProvider::class,
            \Mezzio\Twig\ConfigProvider::class,
        ];
        $this->initApplicationConfigProvider();
        $this->addConfigProvider(new PhpFileProvider(PARS_DIR . '/config/autoload/{{,*.}global,{,*.}local}.php'));
        $this->addConfigProvider(new PhpFileProvider(PARS_DIR . '/config/development.config.php'));
        $aggregator = new ConfigAggregator($this->configProvider, $cacheConfig['config_cache_path']);
        return $aggregator->getMergedConfig();
    }

    /**
     * @param $provider
     * @return $this
     */
    protected function addConfigProvider($provider)
    {
        $this->configProvider[] = $provider;
        return $this;
    }

    protected function initApplicationConfigProvider()
    {
        $this->addConfigProvider(\Pars\Core\ConfigProvider::class);
        $this->addConfigProvider(\Pars\Helper\ConfigProvider::class);
        $this->addConfigProvider(\Pars\Model\ConfigProvider::class);
    }

    /**
     * @param array $dependencies
     * @return AbstractApplicationContainer
     */
    protected abstract function createApplicationContainer(array $dependencies): AbstractApplicationContainer;
}
