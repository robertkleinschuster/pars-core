<?php

namespace Pars\Core\Session\Cache;

use Cache\Adapter\Memcached\MemcachedCachePool;
use Psr\Container\ContainerInterface;

/**
 * Class MemcachedCachePoolFactory
 * @package Pars\Core\Session\Cache
 */
class MemcachedCachePoolFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        $host = $config['mezzio-session-cache']['memcached_host'];
        $port = $config['mezzio-session-cache']['memcached_port'];
        $client = new \Memcached();
        $client->addServer($host, $port);
        $cachePool = new MemcachedCachePool($client);
        $cachePool->setLogger($container->get(\Psr\Log\LoggerInterface::class));
        return $cachePool;
    }
}
