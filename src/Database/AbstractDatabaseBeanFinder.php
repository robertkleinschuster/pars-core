<?php


namespace Pars\Core\Database;

use Pars\Bean\Finder\AbstractBeanFinder;

/**
 * Class AbstractDatabaseBeanFinder
 * @package Pars\Core\Database
 */
abstract class AbstractDatabaseBeanFinder extends AbstractBeanFinder implements ParsDatabaseAdapterAwareInterface
{
    use DatabaseBeanFinderTrait;
}
