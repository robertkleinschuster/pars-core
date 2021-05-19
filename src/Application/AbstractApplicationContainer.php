<?php


namespace Pars\Core\Application;


use Laminas\ServiceManager\ServiceManager;
use Pars\Core\Deployment\CacheClearer;

abstract class AbstractApplicationContainer extends ServiceManager
{

    public function get($name)
    {
        try {
            return parent::get($name);
        } catch (\Exception $exception) {
            $config = [];
            if ($this->has('config')) {
                $config = $this->get('config');
            }
            CacheClearer::clearConfigCache($config);
            throw $exception;
        }
    }

    public abstract function getApplication();

}
