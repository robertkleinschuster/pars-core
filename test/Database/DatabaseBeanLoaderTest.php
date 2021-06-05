<?php


namespace ParsTest\Core\Database;


use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Laminas\Log\Logger;
use Pars\Bean\Finder\BeanFinderInterface;
use Pars\Core\Database\DatabaseBeanLoader;
use Pars\Core\Database\ParsDatabaseAdapter;
use Pars\Pattern\PHPUnit\DefaultTestCase;
use Psr\Log\Test\TestLogger;

class DatabaseBeanLoaderTest extends DefaultTestCase
{

    protected DatabaseBeanLoader $loader;

    protected function setUp(): void
    {
        parent::setUp();
        $connectionParams = array(
            'dbname' => 'mydb',
            'user' => 'user',
            'password' => 'secret',
            'host' => 'localhost',
            'driver' => 'pdo_mysql',
        );
        $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams);

        $adapter = new ParsDatabaseAdapter($conn, new TestLogger());
        $this->loader = new DatabaseBeanLoader($adapter);
    }


    /**
     * @group integration
     * @small
     */
    public function testBuildQuery()
    {
        $this->loader->filter(['test' => 'test'], BeanFinderInterface::FILTER_MODE_AND);
        echo '<pre>';
        print_r($this->loader->buildQuery()->getSQL());
        echo '</pre>';
        exit;
    }
}
