<?php


namespace Pars\Core\Database;


use Pars\Pattern\Exception\CoreException;

class Profiler
{
    protected static ?self $instance = null;

    public function __construct()
    {
        self::$instance = $this;
    }

    public function profilerStart($target)
    {

    }

    /**
     *
     */
    public static function getInstance()
    {
        if (!self::hasInstance()) {
            throw new CoreException('Profiler not initialized.');
        }
        return self::$instance;
    }

    /**
     * @return bool
     */
    public static function hasInstance(): bool
    {
        return isset(self::$instance);
    }

    public function clearProfiles()
    {
        $this->profiles = [];
        $this->currentIndex = 0;
    }

}
