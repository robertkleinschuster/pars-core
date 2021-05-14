<?php

namespace Pars\Core\Cache;

use Cache\Adapter\Common\AbstractCachePool;
use Cache\Adapter\Common\PhpCacheItem;
use Laminas\ConfigAggregator\ArrayProvider;
use Laminas\ConfigAggregator\ConfigAggregator;

class ParsMultiCache extends AbstractCachePool
{
    use ParsCacheTrait;

    /**
     * @type PhpCacheItem[]
     */
    private ?array $cache = null;

    protected string $folder;

    public const DEFAULT_BASE_PATH = 'data/cache/poolmulti/';
    public const SESSION_BASE_PATH = 'data/session/';


    /**
     * @param string $file
     * @param string $basePath
     */
    public function __construct(string $basePath = self::DEFAULT_BASE_PATH)
    {
        if (!is_dir($basePath)) {
            mkdir($basePath);
        }
        $this->folder = $basePath;
        $this->cache = [];
        if ($basePath != self::SESSION_BASE_PATH) {
            $this->savePath($basePath);
        }
    }

    public function set($key, $value, $ttl = null)
    {
        $item = $this->getItem($key);
        $item->set($value);
        $item->expiresAfter($ttl);
        return $this->saveDeferred($item);
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
            if (file_exists($this->folder . DIRECTORY_SEPARATOR . $key . '.php')) {
                unlink($this->folder . DIRECTORY_SEPARATOR . $key . '.php');
            }
        }
        $this->cache = [];
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function clearOneObjectFromCache($key)
    {
        $this->commit();
        unset($this->cache[$key]);
        $this->saveToFile($key);
        return true;
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
        $this->saveToFile($item->getKey());
        return true;
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
        $this->saveToFile($name);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function appendListItem($name, $key)
    {
        $this->cache[$name][] = $key;
        $this->saveToFile($name);
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
            $this->saveToFile($name);
        }
    }


    private function saveToFile(string $key)
    {
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($this->folder . DIRECTORY_SEPARATOR . $key . '.php', true);
        }
        if (file_exists($this->folder . DIRECTORY_SEPARATOR . $key . '.php')) {
            unlink($this->folder . DIRECTORY_SEPARATOR . $key . '.php');
        }
        $agg = new ConfigAggregator(
            [
                new ArrayProvider([ConfigAggregator::ENABLE_CACHE => true]),
                new ArrayProvider($this->cache[$key] ?? []),
            ],
            $this->folder . DIRECTORY_SEPARATOR . $key . '.php'
        );
        if (function_exists('opcache_compile_file')) {
            opcache_compile_file($this->folder . DIRECTORY_SEPARATOR . $key . '.php');
        }
    }

    private function loadFromFile(string $key)
    {
        if ($this->cacheIsset($key)) {
            return $this->cache[$key];
        }
        $agg = new ConfigAggregator(
            [],
            $this->folder . DIRECTORY_SEPARATOR . $key . '.php'
        );
        if (count($agg->getMergedConfig())) {
            $this->cache[$key] = $agg->getMergedConfig();
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
