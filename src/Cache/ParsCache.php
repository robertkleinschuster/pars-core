<?php

namespace Pars\Core\Cache;

use Cache\Adapter\Common\AbstractCachePool;
use Cache\Adapter\Common\PhpCacheItem;
use Laminas\ConfigAggregator\ArrayProvider;
use Laminas\ConfigAggregator\ConfigAggregator;

class ParsCache extends AbstractCachePool
{
    /**
     * @type PhpCacheItem[]
     */
    private ?array $cache = null;

    protected string $file;

    /**
     * @param string $file
     * @param string $basePath
     */
    public function __construct(string $file, $basePath = 'data/cache/pool/')
    {
        if (!is_dir($basePath)) {
            mkdir($basePath);
        }
        $this->file = $basePath . $file . '.php';
    }

    protected function loadFile()
    {
        if ($this->cache == null) {
            $agg = new ConfigAggregator([], $this->file);
            $this->cache = $agg->getMergedConfig();
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function fetchObjectFromCache($key)
    {
        $this->loadFile();
        if (!$this->cacheIsset($key)) {
            return [false, null, [], null];
        }

        $element                       = $this->cache[$key];
        list($data, $tags, $timestamp) = $element;

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
        $this->cache = [];
        $this->saveToFile();
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function clearOneObjectFromCache($key)
    {
        $this->commit();
        unset($this->cache[$key]);
        $this->saveToFile();
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function storeItemInCache(PhpCacheItem $item, $ttl)
    {
        $value  = $item->get();
        if (is_object($value)) {
            $value = clone $value;
        }
        $this->cache[$item->getKey()] = [$value, $item->getTags(), $item->getExpirationTimestamp()];
        $this->saveToFile();
        return true;
    }


    /**
     * {@inheritdoc}
     */
    protected function getList($name)
    {
        $this->loadFile();
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
        $this->saveToFile();
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function appendListItem($name, $key)
    {
        $this->loadFile();
        $this->cache[$name][] = $key;
        $this->saveToFile();
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
            $this->saveToFile();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value, $ttl = null)
    {
        $item = $this->getItem($key);
        $item->set($value);
        $item->expiresAfter($ttl);

        return $this->saveDeferred($item);
    }

    private function saveToFile()
    {
        if (file_exists($this->file)) {
            unlink($this->file);
        }

        $agg = new ConfigAggregator(
            [
                new ArrayProvider([ConfigAggregator::ENABLE_CACHE => true]),
                new ArrayProvider($this->cache),
            ],
            $this->file
        );
        if (function_exists('opcache_compile_file')) {
            opcache_compile_file($this->file);
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
        $this->loadFile();
        return isset($this->cache[$key]);
    }


}
