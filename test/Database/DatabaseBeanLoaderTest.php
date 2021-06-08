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
            'dbname' => 'pars',
            'user' => 'pars',
            'password' => 'pars',
            'host' => '127.0.0.1',
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
        $this->loader->addField('Test.Test_ID')->setKey(true);
        $this->loader->addField('Test.Test_Code');
        $this->loader->addField('TestJoin.TestJoin_ID');
        $this->loader->addField('TestJoin.TestJoin_Code');
        $this->loader->addField('TestJoin.TestJoin2_Code');
        $this->loader->addField('TestJoin2.TestJoin2_Code')->setJoinTableSelf('TestJoin');
        $this->loader->addField('TestJoin2.TestJoin2_Lang');
        $this->loader->addJoinInfo('TestJoin2', 'left', ['TestJoin2_Lang' => 'de']);
        $this->loader->filter(['Test_ID' => 123], BeanFinderInterface::FILTER_MODE_AND);
        $this->loader->exclude(['Test_ID' => 111]);
        $this->loader->search('test');
        $this->loader->order(['Test_Code', 'TestJoin_Code' => BeanFinderInterface::ORDER_MODE_DESC]);
        $this->loader->initByIdMap(['TestJoin2_Code' => 'code']);
        $expected = "SELECT `Test`.`Test_ID` AS 'Test.Test_ID', `Test`.`Test_Code` AS 'Test.Test_Code', `TestJoin`.`TestJoin_ID` AS 'TestJoin.TestJoin_ID', `TestJoin`.`TestJoin_Code` AS 'TestJoin.TestJoin_Code', `TestJoin2`.`TestJoin2_Code` AS 'TestJoin2.TestJoin2_Code', `TestJoin2`.`TestJoin2_Lang` AS 'TestJoin2.TestJoin2_Lang' FROM Test INNER JOIN TestJoin TestJoin ON Test.TestJoin_ID = TestJoin.TestJoin_ID LEFT JOIN TestJoin2 TestJoin2 ON (TestJoin.TestJoin2_Code = TestJoin2.TestJoin2_Code) AND (TestJoin2.TestJoin2_Code = :join_testjoin2_lang_1) WHERE ((Test.Test_ID <> :exclude_test_id_2) AND (Test.Test_ID = :filter_test_id_3) AND (TestJoin2.TestJoin2_Code = :filter_testjoin2_code_4)) OR (Test.Test_ID LIKE :search_test_id_5) OR (Test.Test_Code LIKE :search_test_code_6) OR (TestJoin.TestJoin_ID LIKE :search_testjoin_id_7) OR (TestJoin.TestJoin_Code LIKE :search_testjoin_code_8) OR (TestJoin2.TestJoin2_Code LIKE :search_testjoin2_code_9) OR (TestJoin2.TestJoin2_Lang LIKE :search_testjoin2_lang_10) ORDER BY Test.Test_Code ASC, TestJoin.TestJoin_Code DESC";
        $this->assertEquals($expected, $this->loader->buildQuery(false, true)->getSQL());
    }
}
