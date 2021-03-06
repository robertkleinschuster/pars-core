<?php

namespace Pars\Core\Session\Cache;

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
        return new ParsMultiCache(ParsMultiCache::SESSION_BASE_PATH);
    }
}
