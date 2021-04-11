<?php


namespace Pars\Core\Application;

use Pars\Core\Deployment\CacheClearer;

abstract class AbstractApplicationContainerFactory
{
    public function __invoke()
    {
        $config = $this->getApplicationConfig();
        CacheClearer::registerShutdownErrorFunction($config);
        $dependencies = $config['dependencies'];
        $dependencies['services']['config'] = $config;
        return $this->createApplicationContainer($dependencies);
    }

    /**
     * @return mixed
     */
    protected abstract function getApplicationConfig();

    /**
     * @param array $dependencies
     * @return AbstractApplicationContainer
     */
    protected abstract function createApplicationContainer(array $dependencies): AbstractApplicationContainer;
}
