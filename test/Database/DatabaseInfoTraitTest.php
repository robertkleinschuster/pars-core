<?php


namespace ParsTest\Core\Database;


use Pars\Core\Database\DatabaseColumnDefinition;
use Pars\Core\Database\DatabaseInfoTrait;
use Pars\Pattern\PHPUnit\DefaultTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class DatabaseInfoTraitTest
 * @package ParsTest\Core\Database
 * @coversDefaultClass DatabaseInfoTrait
 */
class DatabaseInfoTraitTest extends DefaultTestCase
{

    /**
     * @var DatabaseInfoTrait|MockObject
     */
    protected $object;

    /**
     *
     */
    protected function setUp(): void
    {
        $this->object = $this->getMockBuilder(DatabaseInfoTrait::class)->getMockForTrait();
    }

    /**
     * @group unit
     * @small
     * @covers DatabaseInfoTrait::addField
     */
    public function testAddField()
    {
        $this->object->addField('Test');
        $dbColumnDefinition_Map = $this->invokeGetProperty($this->object, 'dbColumnDefinition_Map');
        $this->assertArrayHasKey('Test', $dbColumnDefinition_Map);
        /**
         * @var $testDefinition DatabaseColumnDefinition
         */
        $testDefinition = $this->invokeMethod($this->object, 'getDefinition' ,'Test');
        $this->assertEquals('Test', $testDefinition->getColumn());
        $this->assertEquals('Test', $testDefinition->getField());
        $this->assertEquals('Test', $testDefinition->getJoinField());
        $this->assertEquals('Test', $testDefinition->getJoinFieldSelf());
        $this->assertEquals(null, $testDefinition->getTable());
        $this->assertEquals(false, $testDefinition->isKey());
        $this->assertEquals(null, $testDefinition->getJoinTableSelf());
        $this->assertEquals([], $testDefinition->getAdditionalTableList());
        $this->assertEquals([
            'column' => 'Test',
            'joinField' => 'Test',
            'joinFieldSelf' => 'Test',
            'table' => null,
            'isKey' => false,
            'table_List' => [],
            'joinTableSelf' => null,
        ], $testDefinition->toArray());
        $this->assertTrue($this->invokeMethod($this->object, 'hasField', 'Test'));
        $this->assertNull($this->invokeMethod($this->object, 'getTable', 'Test'));
        $this->assertEquals([], $this->invokeMethod($this->object, 'getTable_List'));
        $this->assertEquals(['Test'], $this->invokeMethod($this->object, 'getField_List'));
        $this->assertEquals('Test', $this->invokeMethod($this->object, 'getJoinField', 'Test'));
        $this->assertEquals('Test', $this->invokeMethod($this->object, 'getJoinFieldSelf', 'Test'));
        $this->assertEquals('Test', $this->invokeMethod($this->object, 'getColumn', 'Test'));
        $this->assertEquals('Default', $this->invokeMethod($this->object, 'getJoinTableSelf', 'Test', 'Default'));
    }

    /**
     * @group unit
     * @small
     * @covers DatabaseInfoTrait::addField
     */
    public function testAddField_WithTable()
    {
        $this->object->addField('MyTable.Test');
        $dbColumnDefinition_Map = $this->invokeGetProperty($this->object, 'dbColumnDefinition_Map');
        $this->assertArrayHasKey('Test', $dbColumnDefinition_Map);
        /**
         * @var $testDefinition DatabaseColumnDefinition
         */
        $testDefinition = $this->invokeMethod($this->object, 'getDefinition' ,'Test');
        $this->assertEquals('Test', $testDefinition->getColumn());
        $this->assertEquals('Test', $testDefinition->getField());
        $this->assertEquals('Test', $testDefinition->getJoinField());
        $this->assertEquals('Test', $testDefinition->getJoinFieldSelf());
        $this->assertEquals('MyTable', $testDefinition->getTable());
        $this->assertEquals(false, $testDefinition->isKey());
        $this->assertEquals(null, $testDefinition->getJoinTableSelf());
        $this->assertEquals([], $testDefinition->getAdditionalTableList());
        $this->assertEquals([
            'column' => 'Test',
            'joinField' => 'Test',
            'joinFieldSelf' => 'Test',
            'table' => 'MyTable',
            'isKey' => false,
            'table_List' => [],
            'joinTableSelf' => null,
        ], $testDefinition->toArray());
        $this->assertTrue($this->invokeMethod($this->object, 'hasField', 'Test'));
        $this->assertEquals('MyTable', $this->invokeMethod($this->object, 'getTable', 'Test'));
        $this->assertEquals(['MyTable'], $this->invokeMethod($this->object, 'getTable_List'));
        $this->assertEquals(['Test'], $this->invokeMethod($this->object, 'getField_List'));
        $this->assertEquals('Test', $this->invokeMethod($this->object, 'getJoinField', 'Test'));
        $this->assertEquals('Test', $this->invokeMethod($this->object, 'getJoinFieldSelf', 'Test'));
        $this->assertEquals('Test', $this->invokeMethod($this->object, 'getColumn', 'Test'));
        $this->assertEquals('Default', $this->invokeMethod($this->object, 'getJoinTableSelf', 'Test', 'Default'));

    }

    /**
     * @group unit
     * @small
     * @covers DatabaseInfoTrait::addField
     */
    public function testAddField_WithTable_And_Key()
    {
        $this->object->addField('MyTable.Test')->setKey(true);
        $dbColumnDefinition_Map = $this->invokeGetProperty($this->object, 'dbColumnDefinition_Map');
        $this->assertArrayHasKey('Test', $dbColumnDefinition_Map);
        /**
         * @var $testDefinition DatabaseColumnDefinition
         */
        $testDefinition = $this->invokeMethod($this->object, 'getDefinition' ,'Test');
        $this->assertEquals('Test', $testDefinition->getColumn());
        $this->assertEquals('Test', $testDefinition->getField());
        $this->assertEquals('Test', $testDefinition->getJoinField());
        $this->assertEquals('Test', $testDefinition->getJoinFieldSelf());
        $this->assertEquals('MyTable', $testDefinition->getTable());
        $this->assertEquals(true, $testDefinition->isKey());
        $this->assertEquals(null, $testDefinition->getJoinTableSelf());
        $this->assertEquals([], $testDefinition->getAdditionalTableList());
        $this->assertEquals([
            'column' => 'Test',
            'joinField' => 'Test',
            'joinFieldSelf' => 'Test',
            'table' => 'MyTable',
            'isKey' => true,
            'table_List' => [],
            'joinTableSelf' => null,
        ], $testDefinition->toArray());
        $this->assertTrue($this->invokeMethod($this->object, 'hasField', 'Test'));
        $this->assertEquals('MyTable', $this->invokeMethod($this->object, 'getTable', 'Test'));
        $this->assertEquals(['MyTable'], $this->invokeMethod($this->object, 'getTable_List'));
        $this->assertEquals(['Test'], $this->invokeMethod($this->object, 'getField_List'));
        $this->assertEquals('Test', $this->invokeMethod($this->object, 'getJoinField', 'Test'));
        $this->assertEquals('Test', $this->invokeMethod($this->object, 'getJoinFieldSelf', 'Test'));
        $this->assertEquals('Test', $this->invokeMethod($this->object, 'getColumn', 'Test'));
        $this->assertEquals('Default', $this->invokeMethod($this->object, 'getJoinTableSelf', 'Test', 'Default'));
        $this->assertEquals(['Test'], $this->invokeMethod($this->object, 'getKeyField_List'));
        $this->assertEquals([], $this->invokeMethod($this->object, 'getKeyField_List', 'InvalidTable'));
        $this->assertEquals([], $this->invokeMethod($this->object, 'getKeyField_List', 'InvalidTable', true));
    }


    /**
     * @group unit
     * @small
     * @covers DatabaseInfoTrait::addField
     */
    public function testAddField_MulitpleKeys()
    {
        $this->object->addField('MyTable.Key')->setKey(true);
        $this->object->addField('MyTable.Test');
        $this->object->addField('MyTable2.Key2')->setKey(true);
        $this->object->addField('MyTable2.Test2');
        $this->object->addField('MyTable3.Key3')->setKey(true);
        $this->object->addField('MyTable3.Key33')->setKey(true);
        $this->object->addField('MyTable3.Test3');
        $this->object->addField('MyTable4.Key4')->setKey(true);
        $this->object->addField('MyTable4.Key44')->setKey(true)->setAdditionalTableList(['MyTable3']);
        $this->object->addField('MyTable4.Test4');
        $dbColumnDefinition_Map = $this->invokeGetProperty($this->object, 'dbColumnDefinition_Map');
        $this->assertArrayHasKey('Test', $dbColumnDefinition_Map);
        /**
         * @var $testDefinition DatabaseColumnDefinition
         */
        $testDefinition = $this->invokeMethod($this->object, 'getDefinition' ,'Key');
        $this->assertEquals('Key', $testDefinition->getColumn());
        $this->assertEquals('Key', $testDefinition->getField());
        $this->assertEquals('Key', $testDefinition->getJoinField());
        $this->assertEquals('Key', $testDefinition->getJoinFieldSelf());
        $this->assertEquals('MyTable', $testDefinition->getTable());
        $this->assertEquals(true, $testDefinition->isKey());
        $this->assertEquals(null, $testDefinition->getJoinTableSelf());
        $this->assertEquals([], $testDefinition->getAdditionalTableList());
        $this->assertEquals([
            'column' => 'Key',
            'joinField' => 'Key',
            'joinFieldSelf' => 'Key',
            'table' => 'MyTable',
            'isKey' => true,
            'table_List' => [],
            'joinTableSelf' => null,
        ], $testDefinition->toArray());
        $this->assertTrue($this->invokeMethod($this->object, 'hasField', 'Test'));
        $this->assertEquals('MyTable', $this->invokeMethod($this->object, 'getTable', 'Test'));
        $this->assertEquals('MyTable4', $this->invokeMethod($this->object, 'getTable', 'Key44'));
        $this->assertEquals(['MyTable', 'MyTable2', 'MyTable3', 'MyTable4'], $this->invokeMethod($this->object, 'getTable_List'));
        $this->assertEquals(['Key', 'Test', 'Key2', 'Test2', 'Key3', 'Key33', 'Test3', 'Key4', 'Key44', 'Test4'], $this->invokeMethod($this->object, 'getField_List'));
        $this->assertEquals('Test', $this->invokeMethod($this->object, 'getJoinField', 'Test'));
        $this->assertEquals('Test', $this->invokeMethod($this->object, 'getJoinFieldSelf', 'Test'));
        $this->assertEquals('Test', $this->invokeMethod($this->object, 'getColumn', 'Test'));
        $this->assertEquals('Default', $this->invokeMethod($this->object, 'getJoinTableSelf', 'Test', 'Default'));
        $this->assertEquals(['Key', 'Key2', 'Key3', 'Key33', 'Key4', 'Key44'], $this->invokeMethod($this->object, 'getKeyField_List'));
        $this->assertEquals(['Key'], $this->invokeMethod($this->object, 'getKeyField_List', 'MyTable'));
        $this->assertEquals(['Key2'], $this->invokeMethod($this->object, 'getKeyField_List', 'MyTable2'));
        $this->assertEquals(['Key3', 'Key33', 'Key44'], $this->invokeMethod($this->object, 'getKeyField_List', 'MyTable3'));
        $this->assertEquals(['Key3', 'Key33'], $this->invokeMethod($this->object, 'getKeyField_List', 'MyTable3', true));
        $this->assertEquals([], $this->invokeMethod($this->object, 'getKeyField_List', 'InvalidTable', true));
    }


    /**
     * @group unit
     * @small
     * @covers DatabaseInfoTrait::resetDbInfo
     */
    public function testReset()
    {
        $this->object->addField('MyTable.Key')->setKey(true);
        $this->object->addField('MyTable.Test');
        $this->object->addField('MyTable2.Key2')->setKey(true);
        $this->object->addField('MyTable2.Test2');
        $this->object->addField('MyTable3.Key3')->setKey(true);
        $this->object->addField('MyTable3.Key33')->setKey(true);
        $this->object->addField('MyTable3.Test3');
        $this->object->addField('MyTable4.Key4')->setKey(true);
        $this->object->addField('MyTable4.Key44')->setKey(true)->setAdditionalTableList(['MyTable3']);
        $this->object->addField('MyTable4.Test4');
        $this->object->addJoinInfo('MyTable', 'left', 'MyTable.Key = MyTable2.Key');
        $this->object->addJoinInfo('MyTable2', 'left', 'MyTable.Key = MyTable2.Key');
        $this->object->addJoinInfo('MyTable3', 'left', 'MyTable.Key = MyTable2.Key');
        $this->object->addJoinInfo('MyTable4', 'left', 'MyTable.Key = MyTable2.Key');
        $dbColumnDefinition_Map = $this->invokeGetProperty($this->object, 'dbColumnDefinition_Map');
        $this->assertArrayHasKey('Test', $dbColumnDefinition_Map);

        $dbColumnDefinition_Map = $this->invokeGetProperty($this->object, 'dbColumnDefinition_Map');
        $dbTableJoinDefinition_Map = $this->invokeGetProperty($this->object, 'dbTableJoinDefinition_Map');
        $this->assertCount(10, $dbColumnDefinition_Map);
        $this->assertCount(4, $dbTableJoinDefinition_Map);
        $this->object->resetDbInfo();
        $dbColumnDefinition_Map = $this->invokeGetProperty($this->object, 'dbColumnDefinition_Map');
        $dbTableJoinDefinition_Map = $this->invokeGetProperty($this->object, 'dbTableJoinDefinition_Map');
        $this->assertCount(0, $dbColumnDefinition_Map);
        $this->assertCount(0, $dbTableJoinDefinition_Map);
    }

    /**
     * @group unit
     * @small
     * @covers DatabaseInfoTrait::getJoinOn
     */
    public function testJoinInfo()
    {
        $this->object->addField('MyTable.Key')->setKey(true);
        $this->object->addField('MyTable.Test');
        $this->object->addField('MyTable2.Key2')->setKey(true);
        $this->object->addField('MyTable2.Test2');
        $this->object->addField('MyTable3.Key3')->setKey(true);
        $this->object->addField('MyTable3.Key33')->setKey(true);
        $this->object->addField('MyTable3.Test3');
        $this->object->addField('MyTable4.Key4')->setKey(true);
        $this->object->addField('MyTable4.Key44')->setKey(true)->setAdditionalTableList(['MyTable3']);
        $this->object->addField('MyTable4.Test4');
        $this->object->addJoinInfo('MyTable', 'left', 'MyTable.Key = MyTable2.Key');
        $this->object->addJoinInfo('MyTable2', 'left', 'MyTable.Key = MyTable2.Key');
        $this->object->addJoinInfo('MyTable3', 'left', 'MyTable.Key = MyTable2.Key');
        $this->object->addJoinInfo('MyTable4', 'left', 'MyTable.Key = MyTable2.Key');
        $this->assertEquals(true, $this->invokeMethod($this->object, 'hasJoinInfo', 'MyTable'));
        $this->assertEquals(false, $this->invokeMethod($this->object, 'hasJoinInfo', 'InvalidTable'));
        $this->assertEquals('left', $this->invokeMethod($this->object, 'getJoinType', 'MyTable2'));
        $this->assertEquals('MyTable.Key = MyTable2.Key', $this->invokeMethod($this->object, 'getJoinOn', 'MyTable2'));
    }



}
