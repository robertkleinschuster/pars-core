<?php


namespace Pars\Core\Deployment;


use Pars\Core\Database\Updater\AbstractDatabaseUpdater;
use Psr\Container\ContainerInterface;

/**
 * Class ParsUpdater
 * @package Pars\Core\Deployment
 */
class ParsUpdater implements UpdaterInterface
{
    protected ContainerInterface $container;

    /**
     * ParsUpdater constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    public function update()
    {
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
        $cache = $this->container->get(\Pars\Core\Deployment\CacheClearer::class);
        $cache->clear();
        foreach ($this->getDbUpdaterList() as $dbUpdater) {
            $dbUpdater->executeSilent();
        }
    }

    /**
     * @return AbstractDatabaseUpdater[]
     */
    public function getDbUpdaterList(): array
    {
        return [];
    }

}
