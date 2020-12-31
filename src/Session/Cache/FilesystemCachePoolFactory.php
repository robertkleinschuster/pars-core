<?php

namespace Pars\Core\Session\Cache;

use Cache\Adapter\Filesystem\FilesystemCachePool;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Psr\Container\ContainerInterface;

/**
 * Class FilesystemCachePoolFactory
 * @package Pars\Core\Session\Cache
 */
class FilesystemCachePoolFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        $filesystemAdapter = new Local($config['mezzio-session-cache']['filesystem_folder'], LOCK_NB);
        $filesystem = new Filesystem($filesystemAdapter);
        return new FilesystemCachePool($filesystem);
    }
}
