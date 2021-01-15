<?php

namespace Pars\Core\Session\Cache;

use Cache\Adapter\Filesystem\FilesystemCachePool;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Pars\Core\Cache\ParsMultiCache;
use Psr\Container\ContainerInterface;

/**
 * Class FilesystemCachePoolFactory
 * @package Pars\Core\Session\Cache
 */
class ParsMultiCachePoolFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        return new ParsMultiCache($config['mezzio-session-cache']['filesystem_folder']);
    }
}
