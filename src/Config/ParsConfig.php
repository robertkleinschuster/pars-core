<?php

namespace Pars\Core\Config;

use Cache\Adapter\Common\Exception\CacheException;
use Pars\Core\Cache\ParsCache;
use Pars\Pattern\Exception\CoreException;
use Psr\SimpleCache\InvalidArgumentException;
use Symfony\Component\Uid\Uuid;

class ParsConfig
{
    /**
     * @var array
     */
    protected array $config = [];

    /**
     * @var ParsApplicationConfig|array
     */
    protected ParsApplicationConfig $applicationConfig;

    /**
     * @var ParsCache
     */
    protected ParsCache $cache;

    /**
     * @var ConfigFinderInterface
     */
    protected ConfigFinderInterface $finder;
    protected ConfigProcessorInterface $processor;

    /**
     * @var string
     */
    protected ?string $type = null;

    /**
     * ParsConfig constructor.
     * @param ConfigFinderInterface $finder
     * @param ParsApplicationConfig $applicationConfig
     */
    public function __construct(ConfigFinderInterface $finder, ConfigProcessorInterface $processor, ParsApplicationConfig $applicationConfig)
    {
        $this->finder = $finder;
        $this->processor = $processor;
        $this->applicationConfig = $applicationConfig;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): self
    {
        $this->type = $type;
        $this->cache = new ParsCache("pars-config-{$type}");
        return $this;
    }

    /**
     * @param string $key
     * @param string|null $type
     * @return mixed|void|null
     */
    public function get(string $key, string $type = null)
    {
        $result = null;
        try {
            $restoreType = null;
            if ($type) {
                $restoreType = $this->type;
                $this->setType($type);
            }
            if ($this->type === null) {
                throw new CoreException('No type set for config.');
            }
            if (isset($this->config[$key])) {
                $result = $this->config[$key];
            } elseif ($this->cache->has($key) || $this->cache->get($key) !== null) {
                $result = $this->cache->get($key);
                $this->config[$key] = $result;
            } else {
                $this->config = $this->loadConfig();
                $this->cache->setMultiple($this->config);
                $result = $this->config[$key] ?? null;
            }
            if ($result == null) {
                $result = $this->getFromAppConfig($key);
                $this->cache->set($key, $result);
                $this->config[$key] = $result;
            }
            if ($restoreType) {
                $this->setType($restoreType);
            }
        } catch (\Throwable | InvalidArgumentException $exception) {
            echo $exception->getMessage() .  ' ' . $exception->getFile().  ' ' . $exception->getLine();
        }
        return $result;
    }

    public function getDomainList()
    {
        return [
            $this->getFrontendDomain(),
            $this->getAssetDomain()
        ];
    }

    public function getAssetDomain()
    {
        return $this->get('asset.domain');
    }

    public function getFrontendDomain()
    {
        return $this->get('frontend.domain');
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getFromAppConfig(string $key)
    {
        return $this->getApplicationConfig()->get($key);
    }

    /**
     * @return ParsApplicationConfig
     */
    public function getApplicationConfig(): ParsApplicationConfig
    {
        return $this->applicationConfig;
    }

    /**
     * @return array
     * @throws InvalidArgumentException
     */
    public function toArray(): array
    {
        if (empty($this->config)) {
            $this->config = $this->cache->toArray();
            if (empty($this->config)) {
                $this->config = $this->loadConfig();
                $this->cache->setMultiple($this->config);
            }
        }
        return $this->config;
    }

    /**
     * @return array
     * @throws \Pars\Bean\Type\Base\BeanException
     */
    protected function loadConfig(): array
    {
        try {
            $list = $this->finder->getConfigBeanList();
        } catch (\Throwable $t) {
            $list = $this->finder->getBeanFactory()->getEmptyBeanList();
        }

        try {
            $types = $this->finder->getConfigTypeBeanList()->column('ConfigType_Code_Parent', 'ConfigType_Code');
        } catch (\Throwable $t) {
            $types = [];
        }

        $data = [];
        $keys = [];
        foreach ($list as $item) {
            $data[$item->get('Config_Code')][$item->get('ConfigType_Code')] = $item->get('Config_Value');
            if (!in_array($item->get('Config_Code'), $keys)) {
                $keys[] = $item->get('Config_Code');
            }
        }
        return $this->mergeConfig($types, $data, $keys, $this->type);
    }

    /**
     * @param array $types
     * @param array $data
     * @param array $keys
     * @param string $type
     * @return array
     */
    protected function mergeConfig(array $types, array $data, array $keys, string $type)
    {
        $merged = [];
        foreach ($keys as $key) {
            if (isset($data[$key][$type])) {
                $merged[$key] = $data[$key][$type];
            } elseif (isset($types[$type])) {
                $m = $this->mergeConfig($types, $data, $keys, $types[$type]);
                if (isset($m[$key])) {
                    $merged[$key] = $m[$key];
                }
            }
        }
        return $merged;
    }

    /**
     * @param string $key
     * @param string $value
     * @return $this
     * @throws InvalidArgumentException
     */
    public function set(string $key, string $value, string $type = null)
    {
        try {
            $this->cache->delete($key);
        } catch (\Throwable $exception){}
        if (null == $type) {
            $type = $this->type;
        }
        $this->processor->saveValue($key, $value, $type);
        return $this;
    }

    public function getSecret(bool $noCache = false)
    {
        if ($noCache) {
            $this->cache->delete('secret');
        }
        return $this->get('secret');
    }

    public function generateSecret()
    {
        $this->set('secret', Uuid::v6(), 'base');
    }

    public function getSalt()
    {
        return $this->get('salt');
    }

    public function getUuid()
    {
        return $this->get('uuid');
    }
}
