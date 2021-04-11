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
        return $this->config[$key];
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
