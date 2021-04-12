<?php


namespace Pars\Core\Database;


use Laminas\Db\Adapter\Adapter;
use Pars\Bean\Factory\BeanFactoryInterface;
use Pars\Bean\Finder\AbstractBeanFinder;

/**
 * Class AbstractDatabaseBeanFinder
 * @package Pars\Core\Database
 */
abstract class AbstractDatabaseBeanFinder extends AbstractBeanFinder implements ParsDatabaseAdapterAwareInterface
{
    use ParsDatabaseAdapterAwareTrait;

    public function __construct($adapter)
    {
        if ($adapter instanceof Adapter) {
            $adapter = new ParsDatabaseAdapter($adapter);
        }
        $this->setDatabaseAdapter($adapter);
        $loader = new DatabaseBeanLoader($adapter);
        parent::__construct($loader, $this->createBeanFactory());
        $this->initLoader($loader);
    }

    /**
     * @return BeanFactoryInterface
     */
    protected abstract function createBeanFactory(): BeanFactoryInterface;

    /**
     * @param DatabaseBeanLoader $loader
     * @return mixed
     */
    protected abstract function initLoader(DatabaseBeanLoader $loader);

}
