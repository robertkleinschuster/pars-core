<?php


namespace Pars\Core\Database;


use Pars\Helper\Debug\DebugHelper;
use Pars\Pattern\Exception\CoreException;

class Profiler extends \Laminas\Db\Adapter\Profiler\Profiler
{
    protected static ?self $instance = null;

    public function __construct()
    {
        self::$instance = $this;
    }

    public function profilerStart($target)
    {
        $result = parent::profilerStart($target);
        $this->profiles[$this->currentIndex]['trace'] = DebugHelper::getBacktrace(15, ['profilerStart']);
        return $result;
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

}