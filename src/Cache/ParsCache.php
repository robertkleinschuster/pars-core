<?php

namespace Pars\Core\Cache;

use Cache\Adapter\Common\AbstractCachePool;
use Cache\Adapter\Common\PhpCacheItem;
use Cache\Cache;
use Pars\Bean\Finder\BeanFinderInterface;
use Pars\Bean\Type\Base\AbstractBaseBean;
use Pars\Bean\Type\Base\AbstractBaseBeanList;
use Pars\Bean\Type\Base\BeanInterface;
use Pars\Bean\Type\Base\BeanListInterface;
use Pars\Helper\String\StringHelper;

/**
 * Class ParsCache
 * @package Pars\Core\Cache
 */
class ParsCache extends AbstractCachePool implements ParsCacheInterface
{

    use ParsCacheTrait;

    public const DEFAULT_BASE_PATH = '/pool';
    public const IMAGE_BASE_PATH = '/image';

    /**
     * @type PhpCacheItem[]
     */
    private ?array $cache = null;

    protected string $file;

    /**
     * @param string $file
     * @param string $basePath
     */
    public function __construct(string $file, string $basePath = self::DEFAULT_BASE_PATH)
    {
        $file = StringHelper::slugify($file);
        $this->file = PARS_CACHE_DIR . $basePath . '/' . $file;
        $this->savePath($basePath);
        register_shutdown_function([$this, 'commit']);
    }

    protected function loadFile()
    {
        if ($this->cache == null) {
            $cache = new Cache($this->file);
            $this->cache = $cache->get('cache');
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
        unset($this->cache[$key]);
        $this->commit();
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
    public function setBean(string $key, BeanInterface $bean, int $ttl = null)
    {
        $this->set($key, $bean->toArray(true), $ttl);
        return $this;
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed|AbstractBaseBean|BeanInterface|null
     * @throws \Pars\Bean\Type\Base\BeanException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getBean(string $key, BeanInterface $default = null): ?BeanInterface
    {
        $result = $default;
        $data = $this->get($key);
        if ($data instanceof BeanInterface) {
            return $data;
        }
        if (is_array($data)) {
            $result = AbstractBaseBean::createFromArray($data);
        }
        return $result;
    }

    /**
     * @param $key
     * @param BeanInterface $beanList
     * @return $this
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function setBeanList($key, BeanListInterface $beanList, int $ttl = null)
    {
        $this->set($key, $beanList->toArray(true), $ttl);
        return $this;
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed|AbstractBaseBeanList|BeanListInterface|null
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getBeanList(string $key, BeanListInterface $default = null): ?BeanListInterface
    {
        $result = $default;
        $data = $this->get($key);
        if ($data instanceof BeanListInterface) {
            return $data;
        }
        if (is_array($data)) {
            $result = AbstractBaseBeanList::createFromArray($data);
        }
        return $result;
    }

    /**
     * @param $key
     * @param BeanFinderInterface $finder
     * @return $this
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function setBeanFinderResult($key, BeanFinderInterface $finder, int $ttl = null)
    {
        $this->setBeanList($key, $finder->getBeanListDecorator(), $ttl);
        return $this;
    }

    private function saveToFile()
    {
        $result = false;
        try {
            $cache = new Cache($this->file);
            $cache->set('cache', $this->cache);
            $result = true;
        } catch (\Throwable $exception) {
            syslog(LOG_ERR, 'Could not save cache ' . $this->file . ' ' . $exception->getMessage());
        }
        return $result;
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
        return $result;
    }
}
