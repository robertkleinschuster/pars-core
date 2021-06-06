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
        $expected = "SELECT Test.Test_ID, Test.Test_Code, TestJoin.TestJoin_ID, TestJoin.TestJoin_Code, TestJoin2.TestJoin2_Code, TestJoin2.TestJoin2_Lang FROM Test INNER JOIN TestJoin TestJoin ON Test.TestJoin_ID = TestJoin.TestJoin_ID LEFT JOIN TestJoin2 TestJoin2 ON (TestJoin.TestJoin2_Code = TestJoin2.TestJoin2_Code) AND (TestJoin2.TestJoin2_Lang = :join_testjoin2_lang_1_de) WHERE ((Test.Test_ID <> :exclude_test.test_id_2_111) AND (Test.Test_ID = :filter_test.test_id_3_123) AND (TestJoin2.TestJoin2_Code = :filter_testjoin2.testjoin2_code_4_code)) OR (Test.Test_ID LIKE :search_test.test_id_5_) OR (Test.Test_Code LIKE :search_test.test_code_6_) OR (TestJoin.TestJoin_ID LIKE :search_testjoin.testjoin_id_7_) OR (TestJoin.TestJoin_Code LIKE :search_testjoin.testjoin_code_8_) OR (TestJoin2.TestJoin2_Code LIKE :search_testjoin2.testjoin2_code_9_) OR (TestJoin2.TestJoin2_Lang LIKE :search_testjoin2.testjoin2_lang_10_) ORDER BY Test.Test_Code ASC, TestJoin.TestJoin_Code DESC";
        $this->assertEquals($expected, $this->loader->buildQuery(false, true)->getSQL());
    }
}
