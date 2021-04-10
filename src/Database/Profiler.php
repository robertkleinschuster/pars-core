<?php


namespace Pars\Core\Database;


use Pars\Helper\Debug\DebugHelper;

class Profiler extends \Laminas\Db\Adapter\Profiler\Profiler
{
    public function profilerStart($target)
    {
        $result = parent::profilerStart($target);
        $this->profiles[$this->currentIndex]['trace'] = DebugHelper::getBacktrace(15, ['profilerStart']);
        return $result;
    }

}
