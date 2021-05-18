<?php

namespace Pars\Core\Cache;

use Cache\Adapter\Common\AbstractCachePool;
use Cache\Adapter\Common\PhpCacheItem;
use Cocur\Slugify\Slugify;
use Laminas\ConfigAggregator\ArrayProvider;
use Laminas\ConfigAggregator\ConfigAggregator;
use Pars\Bean\Finder\BeanFinderInterface;
use Pars\Bean\Type\Base\BeanInterface;
use Pars\Helper\Filesystem\FilesystemHelper;
use Pars\Helper\String\StringHelper;

class ParsCache extends AbstractCachePool
{

    use ParsCacheTrait;

    public const DEFAULT_BASE_PATH = 'data/cache/pool/';
    public const IMAGE_BASE_PATH = 'data/cache/image/';

    /**
     * @type PhpCacheItem[]
     */
    private ?array $cache = null;

    protected string $file;

    /**
     * @param string $file
     * @param string $basePath
     */
    public function __construct(string $file, $basePath = self::DEFAULT_BASE_PATH)
    {
        $file = StringHelper::slugify($file);
        $this->file = $basePath . $file . '.php';
        $this->savePath($basePath);
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

        $element = $this->cache[$key];
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
        $value = $item->get();
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
        $this->saveDeferred($item);
        return $this;
    }

    /**
     * @param $key
     * @param BeanInterface $bean
     * @return $this
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function setBean($key, BeanInterface $bean)
    {
        $this->set($key, $bean);
        return $this;
    }

    /**
     * @param $key
     * @param BeanInterface $beanList
     * @return $this
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function setBeanList($key, BeanInterface $beanList)
    {
        $this->set($key, $beanList->toArray());
        return $this;
    }

    /**
     * @param $key
     * @param BeanFinderInterface $finder
     * @return $this
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function setBeanFinderResult($key, BeanFinderInterface $finder)
    {
        $this->set($key, $finder->getBeanList()->toArray());
        return $this;
    }

    private function saveToFile()
    {
        try {
            $filename = FilesystemHelper::getPath($this->file);

            if (file_exists($filename)) {
                if (function_exists('opcache_invalidate')) {
                    opcache_invalidate($filename, true);
                }
                unlink($filename);
            }

            $agg = new ConfigAggregator(
                [
                    new ArrayProvider([ConfigAggregator::ENABLE_CACHE => true]),
                    new ArrayProvider($this->cache),
                ],
                $filename
            );

            if (function_exists('opcache_compile_file')) {
                opcache_compile_file($filename);
            }
        } catch (\Throwable $exception) {
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

    /**
     * @return PhpCacheItem[]
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function toArray(): array
    {
        $this->loadFile();
        $result = [];
        foreach ($this->cache as $key => $value) {
            $result[$key] = $this->get($key);
        }
        unset($result[ConfigAggregator::ENABLE_CACHE]);
        return $result;
    }
}
