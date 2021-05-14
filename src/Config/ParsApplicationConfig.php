<?php


namespace Pars\Core\Config;


class ParsApplicationConfig
{
    protected array $config = [];

    /**
     * ParsApplicationConfig constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        return $this->getRecursiveValue($this->config, $key);
    }

    /**
     * @param $data
     * @param $key_path
     * @return mixed
     */
    protected function getRecursiveValue($data, $key_path)
    {
        if (!is_array($key_path)) {
            $key_path = explode('.', $key_path);
        }
        if (count($key_path) == 0) {
            return $data;
        }
        $key = array_shift($key_path);
        return $this->getRecursiveValue($data[$key] ?? null, $key_path);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($this->config[$key]);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->config;
    }
}
