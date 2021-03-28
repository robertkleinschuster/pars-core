<?php

namespace Pars\Core\Config;

use Laminas\Db\Adapter\AdapterInterface;
use Pars\Core\Cache\ParsCache;
use Pars\Model\Config\ConfigBeanFinder;
use Pars\Model\Config\Type\ConfigTypeBeanFinder;

class ParsConfig
{
    /**
     * @var AdapterInterface
     */
    protected AdapterInterface $adapter;

    /**
     * @var array
     */
    protected array $config = [];

    /**
     * @var ParsCache
     */
    protected ParsCache $cache;

    /**
     * @var ConfigBeanFinder
     */
    protected ConfigBeanFinder $finder;

    /**
     * @var string
     */
    protected string $type;

    /**
     * ParsConfig constructor.
     * @param AdapterInterface $adapter
     * @param string $type
     */
    public function __construct(AdapterInterface $adapter, string $type = 'base')
    {
        $this->adapter = $adapter;
        $this->cache = new ParsCache("pars-config-{$type}");
        $this->finder = new ConfigBeanFinder($adapter);
        $this->type = $type;
    }

    /**
     * @param string $key
     * @return mixed|void|null
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function get(string $key)
    {
        if (isset($this->config[$key])) {
            return $this->config[$key];
        }
        if ($this->cache->has($key)) {
            $this->config[$key] = $this->cache->get($key);
            return $this->config[$key];
        }
        $this->config = $this->loadConfig();
        $this->cache->setMultiple($this->config);
        return $this->config[$key] ?? null;
    }

    /**
     * @return array
     * @throws \Psr\SimpleCache\InvalidArgumentException
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
     * @throws \Niceshops\Bean\Type\Base\BeanException
     */
    protected function loadConfig(): array
    {
        $list = $this->finder->getBeanListDecorator();
        $types = (new ConfigTypeBeanFinder($this->adapter))
            ->getBeanList()
            ->column('ConfigType_Code_Parent', 'ConfigType_Code');

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
}
