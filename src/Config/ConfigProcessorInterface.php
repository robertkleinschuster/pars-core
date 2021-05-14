<?php


namespace Pars\Core\Config;


interface ConfigProcessorInterface
{
    public function saveValue(string $key, string $value, string $type);
}
