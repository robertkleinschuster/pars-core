<?php

namespace Pars\Core\Cache;

use Cache\Adapter\Common\AbstractCachePool;
use Cache\Adapter\Common\PhpCacheItem;
use Cache\Cache;
use Pars\Helper\String\StringHelper;

/**
 * Class ParsMultiCache
 * @package Pars\Core\Cache
 */
class ParsMultiCache extends AbstractCachePool implements ParsCacheInterface
{
    use ParsCacheTrait;

    /**
     * @type PhpCacheItem[]
     */
    private ?array $cache = null;

    protected string $folder;

    public const DEFAULT_BASE_PATH = '/poolmulti';
    public const SESSION_BASE_PATH = PARS_SESSION_DIR;


    /**
     * @param string $basePath
     */
    public function __construct(string $basePath = self::DEFAULT_BASE_PATH)
    {
        $this->cache = [];
        if ($basePath == self::SESSION_BASE_PATH) {
            $this->folder = self::SESSION_BASE_PATH;
        } else {
            $basePath = StringHelper::slugify($basePath);
            $this->folder = PARS_CACHE_DIR . $basePath;
            $this->savePath($basePath);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function fetchObjectFromCache($key)
    {
        $this->loadFromFile($key);
        if (!$this->cacheIsset($key) || !isset($this->cache[$key])) {
            return [false, null, [], null];
        }

        try {
            $element = $this->cache[$key];
            list($data, $tags, $timestamp) = $element;
        } catch (\Exception $exception) {
            return [false, null, [], null];
        }


        if (is_object($data)) {
            $data = clone $data;
        }

        return [true, $data, $tags, $timestamp];
    }

    /**
     * {@inheritdoc}
     */
    protected function clearAllObjectsFromCache()
    {
        foreach ($this->cache as $key => $value) {
            $filename = $this->getFilename($key);
            if (file_exists($filename)) {
                unlink($filename);
            }
        }
        $this->cache = [];
        return true;
    }

    protected function getFilename($key)
    {
        return $this->folder . DIRECTORY_SEPARATOR . $key;
    }

    /**
     * {@inheritdoc}
     */
    protected function clearOneObjectFromCache($key)
    {
        unset($this->cache[$key]);
        return $this->commit();
    }

    /**
     * {@inheritdoc}
     */
    protected function storeItemInCache(PhpCacheItem $item, $ttl)
    {
        $value = $item->get();
        if (is_object($value)) {
            $value = clone $value;
        }
        $this->cache[$item->getKey()] = [$value, $item->getTags(), $item->getExpirationTimestamp()];
        return $this->saveToFile($item->getKey());
    }


    /**
     * {@inheritdoc}
     */
    protected function getList($name)
    {
        if (!isset($this->cache[$name])) {
            $this->cache[$name] = [];
        }

        return $this->cache[$name];
    }

    /**
     * {@inheritdoc}
     */
    protected function removeList($name)
    {
        unset($this->cache[$name]);
        return $this->saveToFile($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function appendListItem($name, $key)
    {
        $this->cache[$name][] = $key;
        return $this->saveToFile($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function removeListItem($name, $key)
    {
        if (isset($this->cache[$name])) {
            foreach ($this->cache[$name] as $i => $item) {
                if ($item === $key) {
                    unset($this->cache[$name][$i]);
                }
            }
            return $this->saveToFile($name);
        }
        return true;
    }

    /**
     * @param string $key
     * @throws \Cache\Exception\CacheException
     */
    private function saveToFile(string $key)
    {
        $result = false;
        try {
            $filename = $this->getFilename($key);
            $cache = new Cache($filename);
            $cache->set($key, $this->cache[$key]);
            $glob = glob($filename . '/*');
            foreach ($glob as $item) {
                if (function_exists('opcache_invalidate')) {
                    opcache_invalidate($item);
                }
            }
            $result = true;
        } catch (\Throwable $exception) {
            syslog(LOG_ERR, 'Could not save cache ' . $filename . ' ' . $exception->getMessage());
        }
        return $result;
    }

    /**
     * @param string $key
     * @throws \Cache\Exception\CacheException
     */
    private function loadFromFile(string $key)
    {
        if (!$this->cacheIsset($key)) {
            $file = $this->getFilename($key);
            $cache = new Cache($file);
            if ($cache->has($key)) {
                $this->cache[$key] = $cache->get($key);
            }
        }
    }


    /**
     * Checking if given keys exists and is valid.
     *
     * @param string $key
     *
     * @return bool
     */
    private function cacheIsset($key)
    {
        return isset($this->cache[$key]);
    }
}
