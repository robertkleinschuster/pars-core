<?php


namespace Pars\Core\Database;


use Pars\Bean\Factory\BeanFactoryInterface;
use Pars\Bean\Type\Base\BeanException;

trait DatabaseBeanFinderTrait
{
    use ParsDatabaseAdapterAwareTrait;

    public function __construct(ParsDatabaseAdapter $adapter, bool $initLinked = true)
    {
        $this->setDatabaseAdapter($adapter);
        $loader = new DatabaseBeanLoader($adapter);
        parent::__construct($loader, $this->createBeanFactory());
        $this->initLoader($loader);
        if ($initLinked) {
            $this->initLinkedFinder();
        }
    }

    protected function initLinkedFinder() {

    }


    /**
     * @return BeanFactoryInterface
     */
    protected function createBeanFactory(): BeanFactoryInterface {
        $class = str_replace('Finder', 'Factory', static::class);
        return new $class();
    }

    /**
     * @param DatabaseBeanLoader $loader
     * @return mixed
     */
    protected abstract function initLoader(DatabaseBeanLoader $loader);

}
