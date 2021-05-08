<?php


namespace Pars\Core\Session;


use Mezzio\Session\Cache\Exception\MissingDependencyException;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;

class ParsSessionPersistenceFactory
{
    /**
     * @todo Use explicit return type hint for 2.0
     * @return ParsSessionPersistence
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $config = $config['mezzio-session-cache'] ?? [];

        $cacheService = $config['cache_item_pool_service'] ?? CacheItemPoolInterface::class;

        if (! $container->has($cacheService)) {
            throw MissingDependencyException::forService($cacheService);
        }

        $cookieName     = $config['cookie_name'] ?? 'PHPSESSION';
        $cookieDomain   = $config['cookie_domain'] ?? null;
        $cookiePath     = $config['cookie_path'] ?? '/';
        $cookieSecure   = $config['cookie_secure'] ?? false;
        $cookieHttpOnly = $config['cookie_http_only'] ?? false;
        $cookieSameSite = $config['cookie_same_site'] ?? 'Lax';
        $cacheLimiter   = $config['cache_limiter'] ?? 'nocache';
        $cacheExpire    = $config['cache_expire'] ?? 10800;
        $lastModified   = $config['last_modified'] ?? null;
        $persistent     = $config['persistent'] ?? false;

        return new ParsSessionPersistence(
            $container->get($cacheService),
            $cookieName,
            $cookiePath,
            $cacheLimiter,
            $cacheExpire,
            $lastModified,
            $persistent,
            $cookieDomain,
            $cookieSecure,
            $cookieHttpOnly,
            $cookieSameSite
        );
    }
}
