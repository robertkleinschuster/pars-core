<?php


namespace Pars\Core\Database;


use Laminas\Db\Adapter\AdapterAwareInterface;
use Laminas\Db\Adapter\AdapterAwareTrait;
use Laminas\Db\Adapter\AdapterInterface;

class ParsDbAdapter implements AdapterAwareInterface
{
    use AdapterAwareTrait;


    /**
     * ParsDbAdapter constructor.
     */
    public function __construct(AdapterInterface $adapter)
    {
        $this->setDbAdapter($adapter);
    }

    /**
     * @return AdapterInterface
     */
    public function getDbAdapter(): AdapterInterface
    {
        return $this->adapter;
    }
}
