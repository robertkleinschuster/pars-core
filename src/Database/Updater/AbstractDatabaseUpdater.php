<?php

namespace Pars\Core\Database\Updater;

use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Schema\Table;
use Pars\Bean\Finder\BeanFinderInterface;
use Pars\Bean\Processor\BeanProcessorInterface;
use Pars\Core\Container\ParsContainer;
use Pars\Core\Container\ParsContainerAwareTrait;
use Pars\Helper\String\StringHelper;
use Pars\Helper\Validation\ValidationHelperAwareInterface;
use Pars\Helper\Validation\ValidationHelperAwareTrait;
use Pars\Pattern\Exception\CoreException;

abstract class AbstractDatabaseUpdater implements ValidationHelperAwareInterface
{
    use ValidationHelperAwareTrait;
    use ParsContainerAwareTrait;

    public const MODE_PREVIEW = 'preview';
    public const MODE_EXECUTE = 'execute';

    private const PREFIX_UPDATE = 'update';

    /**
     * @var string
     */
    private $mode;

    protected $metadata;

    /**
     * @var array
     */
    protected $existingTableList;

    abstract public function getCode(): string;

    /**
     * /**
     * AbstractDatabaseUpdater constructor.
     * @param ParsContainer $parsContainer
     * @throws \Pars\Pattern\Exception\CoreException
     */
    public function __construct(ParsContainer $parsContainer)
    {
        $this->setParsContainer($parsContainer);
        $this->existingTableList = $this->getDatabaseAdapter()->getSchemaManager()->listTables();
    }

    /**
     * @return \Pars\Core\Database\ParsDatabaseAdapter
     */
    public function getDatabaseAdapter()
    {
        return $this->getParsContainer()->getDatabaseAdapter();
    }

    /**
     * @param string $table
     * @param $constraint
     * @return bool
     * @throws \Doctrine\DBAL\Exception
     */
    public function hasConstraints(string $table, $constraint)
    {
        $keys = $this->getDatabaseAdapter()->getSchemaManager()->listTableForeignKeys($table);
        $idxs = $this->getDatabaseAdapter()->getSchemaManager()->listTableIndexes($table);
        return in_array($constraint, $keys) || in_array($constraint, $idxs);
    }

    /**
     * @return string
     */
    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * @param string $mode
     *
     * @return $this
     */
    public function setMode(string $mode): self
    {
        $this->mode = $mode;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasMode(): bool
    {
        return $this->mode !== null;
    }

    /**
     * @return bool
     */
    public function isPreview(): bool
    {
        return $this->hasMode() && $this->getMode() == self::MODE_PREVIEW;
    }

    /**
     * @return bool
     */
    public function isExecute(): bool
    {
        return $this->hasMode() && $this->getMode() == self::MODE_EXECUTE;
    }

    /**
     * @return array
     */
    public function getUpdateMethodList(): array
    {
        $methods = [];
        $methods[] = 'baseTables';
        foreach (get_class_methods(static::class) as $method) {
            if (strpos($method, self::PREFIX_UPDATE) === 0) {
                $methods[] = $method;
            }
        }
        return $methods;
    }

    /**
     * @return array
     */
    public function getPreviewMap(): array
    {
        $this->setMode(self::MODE_PREVIEW);
        $resultMap = [];
        foreach ($this->getUpdateMethodList() as $method) {
            $resultMap[$method] = $this->executeMethod($method);
        }
        return $resultMap;
    }

    public function execute(array $methods): array
    {
        $this->setMode(self::MODE_EXECUTE);
        $methodList = $this->getUpdateMethodList();
        $resultMap = [];
        foreach ($methods as $method => $enabled) {
            if (boolval($enabled) && in_array($method, $methodList)) {
                $resultMap[$method] = $this->executeMethod($method);
            }
        }
        return $resultMap;
    }

    public function executeSilent()
    {
        $this->setMode(self::MODE_EXECUTE);
        $methodList = $this->getUpdateMethodList();
        $resultMap = [];
        foreach ($methodList as $method) {
            $resultMap[$method] = $this->executeMethod($method);
        }
        return $resultMap;
    }

    /**
     * @param string $method
     */
    protected function executeMethod(string $method)
    {
        try {
            return $this->{$method}();
        } catch (\Throwable $ex) {
            $this->getValidationHelper()->addError($method, $ex->getMessage());
            $this->getParsContainer()->getLogger()->error($ex->getMessage());
            return $ex->getMessage();
        }
    }

    protected function query($statement)
    {
        if ($this->isExecute()) {
            if (!is_array($statement)) {
                $statement = [$statement];
            }
            foreach ($statement as $item) {
                $this->getDatabaseAdapter()->getConnection()->executeStatement($item);
            }
        }
        foreach ($statement as &$item) {
            $item = str_replace(PHP_EOL, '<br>', $item);
        }

        return $statement;
    }


    /**
     * @param string $table
     * @param string $column
     * @return array
     */
    protected function getKeyList(string $table, $column)
    {
        $builder = $this->getDatabaseAdapter()->getQueryBuilder();
        $col = $column;
        if (!is_array($column)) {
            $column = [$column];
        }
        $builder->select(...$column);
        $builder->from($table);

        $result = [];
        if (is_array($col)) {
            $dbdata = $builder->fetchAllAssociative();
            foreach ($col as $item) {
                $result[$item] = array_column($dbdata, $item);
            }
        } else {
            $dbdata = $builder->fetchAllAssociative();
            $result = array_column($dbdata, $col);
        }
        return $result;
    }

    /**
     * @param string $table
     * @param string|array $keyColumn
     * @param array $data_Map
     * @param bool $noUpdate
     * @param array $forceUpdateColumns
     * @return array
     */
    protected function saveDataMap(
        string $table,
        $keyColumn,
        array $data_Map,
        bool $noUpdate = false,
        array $forceUpdateColumns = []
    )
    {
        $existingKey_List = $this->getKeyList($table, $keyColumn);
        $result = [];
        foreach ($data_Map as $item) {
            foreach ($item as $key => $value) {
                if (is_array($value)) {
                    $item[$key] = json_encode($value);
                }
            }
            $builder = $this->getDatabaseAdapter()->getQueryBuilder();
            if ($this->isUpdate($item, $keyColumn, $existingKey_List)) {
                if (!$noUpdate) {
                    $builder->update($table);
                    if (is_array($keyColumn)) {
                        foreach ($keyColumn as $column) {
                            $builder->andWhere($builder->expr()->eq($column, $builder->createNamedParameter($item[$column])));
                            unset($item[$column]);
                        }
                    } else {
                        $builder->andWhere($builder->expr()->eq($keyColumn, $builder->createNamedParameter($item[$keyColumn])));
                        unset($item[$keyColumn]);
                    }
                    foreach ($item as $key => $value) {
                        $builder->set($key, $builder->createNamedParameter($value));
                    }
                    $result[] = $builder->getSQL();
                    if ($this->isExecute()) {
                        $builder->executeStatement();
                    }
                } elseif (count(array_intersect(array_keys($item), $forceUpdateColumns))) {
                    $builder->update($table);
                    if (is_array($keyColumn)) {
                        foreach ($keyColumn as $column) {
                            $builder->andWhere($builder->expr()->eq($column, $builder->createNamedParameter($item[$column])));
                            unset($item[$column]);
                        }
                    } else {
                        $builder->andWhere($builder->expr()->eq($keyColumn, $builder->createNamedParameter($item[$keyColumn])));
                        unset($item[$keyColumn]);
                    }
                    $data = [];
                    foreach ($forceUpdateColumns as $forceUpdateColumn) {
                        if (isset($item[$forceUpdateColumn])) {
                            $data[$forceUpdateColumn] = $item[$forceUpdateColumn];
                        }
                    }
                    foreach ($data as $key => $value) {
                        $builder->set($key, $builder->createNamedParameter($value));
                    }
                    $result[] = $builder->getSQL();
                    if ($this->isExecute()) {
                        $builder->executeStatement();
                    }
                }
            } else {
                $builder->insert($table);
                foreach ($item as $key => &$value) {
                    $value = $builder->createNamedParameter($value);
                }
                $builder->values($item);
                $result[] = $builder->getSQL();
                if ($this->isExecute()) {
                    $builder->executeStatement();
                }
            }
        }
        $builder = $this->getDatabaseAdapter()->getQueryBuilder();
        if (is_array($keyColumn)) {
            $key_List = [];
            foreach ($existingKey_List as $existingKey => $existingKeys) {
                foreach ($existingKeys as $key => $existingValue) {
                    foreach ($keyColumn as $column) {
                        $key_List[$key][$existingKey] = $existingValue;
                    }
                }
            }
            $data_Map_Del = [];
            foreach ($data_Map as $k => $item) {
                foreach ($keyColumn as $column) {
                    $data_Map_Del[$k][$column] = $item[$column];
                }
            }

            foreach ($key_List as $item) {
                if (!in_array($item, $data_Map_Del)) {
                    $builder->delete($table);
                    foreach ($item as $key => $v) {
                        $builder->andWhere($builder->expr()->eq($key, $builder->createNamedParameter($v)));
                    }
                    $result[] = $builder->getSQL();
                    if ($this->isExecute()) {
                        $builder->executeStatement();
                    }
                }
            }
        } else {
            foreach ($existingKey_List as $id) {
                $newKey_List = array_column($data_Map, $keyColumn);
                if (!in_array($id, $newKey_List)) {
                    $builder->delete($table);
                    $builder->andWhere($builder->expr()->eq($keyColumn, $builder->createNamedParameter($id)));
                    $result[] = $builder->getSQL();
                    if ($this->isExecute()) {
                        $builder->executeStatement();
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @param $item
     * @param $keyColumn
     * @param $existingKey_List
     * @return bool
     */
    protected function isUpdate($item, $keyColumn, $existingKey_List): bool
    {
        if (is_array($keyColumn)) {
            foreach ($item as $key => $value) {
                if (!in_array($key, $keyColumn)) {
                    unset($item[$key]);
                }
            }
            $key_List = [];
            foreach ($existingKey_List as $existingKey => $existingKeys) {
                foreach ($existingKeys as $key => $existingValue) {
                    foreach ($keyColumn as $column) {
                        $key_List[$key][$existingKey] = $existingValue;
                    }
                }
            }
            return in_array($item, $key_List);
        } else {
            return in_array($item[$keyColumn], $existingKey_List);
        }
    }

    protected function saveBeanData(
        BeanFinderInterface $finder,
        BeanProcessorInterface $processor,
        string $key,
        array $data
    )
    {
        $finder->filterValue($key, $data[$key]);
        if ($finder->count() == 0 && $this->isExecute()) {
            $factory = $finder->getBeanFactory();
            $beanList = $factory->getEmptyBeanList();
            $bean = $factory->getEmptyBean($data);
            $bean->fromArray($data);
            $beanList->push($bean);
            $processor->setBeanList($beanList);
            $processor->save();
            if ($processor instanceof ValidationHelperAwareInterface) {
                if ($processor->getValidationHelper()->hasError()) {
                    throw new CoreException($processor->getValidationHelper()->getSummary());
                }
            }
        }
        return !$finder->count();
    }

    protected function getLocaleDefault()
    {
        return $this->getParsContainer()->getConfig()->get('locale.default');
    }

    protected const TYPE_STRING = 'string';
    protected const TYPE_BOOLEAN = 'boolean';
    protected const TYPE_INTEGER = 'integer';
    protected const TYPE_TEXT = 'text';
    protected const TYPE_JSON = 'json';
    protected const TYPE_DATETIME = 'datetime';

    public function getSchemaManager()
    {
        return $this->getDatabaseAdapter()->getSchemaManager();
    }

    protected function getSchema()
    {
        static $schema = null;
        if (null === $schema) {
            $schema = clone $this->getSchemaManager()->createSchema();
        }
        return $schema;
    }

    protected function getCurrentSchema(bool $update = false)
    {
        static $schema = null;
        if (null === $schema) {
            $schema = $this->getSchemaManager()->createSchema();
        }

        $result = clone $schema;
        if ($update) {
            $schema = $this->getSchemaManager()->createSchema();
        }
        return $result;
    }

    protected function getPlatform()
    {
        return $this->getDatabaseAdapter()->getConnection()->getDatabasePlatform();
    }

    protected function getTableStatement(string $tableName, $schema = null)
    {
        if (null == $schema) {
            $schema = $this->getSchema();
        }
        if ($schema->hasTable($tableName)) {
            $table = $schema->getTable($tableName);
        } else {
            $table = $schema->createTable($tableName);
        }
        return $table;
    }


    protected function initAsTypeTable(Table $table)
    {
        $tableName = $table->getName();
        $this->addColumnToTable($table, "{$tableName}_Code");
        $this->addColumnToTable($table, "{$tableName}_Template", null, true);
        $this->addColumnToTable($table, "{$tableName}_Active");
        $this->addColumnToTable($table, "{$tableName}_Order");
        $this->addPrimaryKeyToTable($table, "{$tableName}_Code");
        $this->addDefaultColumnsToTable($table);
    }

    protected function initAsStateTable(Table $table)
    {
        $tableName = $table->getName();
        $this->addColumnToTable($table, "{$tableName}_Code");
        $this->addColumnToTable($table, "{$tableName}_Active");
        $this->addColumnToTable($table, "{$tableName}_Order");
        $this->addPrimaryKeyToTable($table, "{$tableName}_Code");
        $this->addDefaultColumnsToTable($table);
    }

    protected function addColumnToTable(Table $table, string $name, string $type = null, bool $nullable = null, bool $deleteCascade = false)
    {
        $default = 0;

        if (null === $type) {
            if (StringHelper::endsWith($name, '_Order')) {
                $type = self::TYPE_INTEGER;
                $default = 0;
            }
            if (StringHelper::endsWith($name, '_Name')) {
                $type = self::TYPE_STRING;
            }
            if (StringHelper::endsWith($name, '_Reference')) {
                $type = self::TYPE_STRING;
            }
            if (StringHelper::endsWith($name, '_Template')) {
                $type = self::TYPE_STRING;
            }
            if (StringHelper::endsWith($name, '_Active')) {
                $type = self::TYPE_BOOLEAN;
                $default = 1;
            }
            if (StringHelper::endsWith($name, '_Code')) {
                $type = self::TYPE_STRING;
            }
            if (StringHelper::endsWith($name, '_Data')) {
                $type = self::TYPE_JSON;
                if (null === $nullable) {
                    $nullable = true;
                }
            }
            if (StringHelper::endsWith($name, '_Text')) {
                $type = self::TYPE_TEXT;
                if (null === $nullable) {
                    $nullable = true;
                }
            }
            if (StringHelper::endsWith($name, '_ID')) {
                $type = self::TYPE_INTEGER;
            }
        }

        if ($table->hasColumn($name)) {
            $column = $table->getColumn($name);
        } else {
            $column = $table->addColumn($name, $type);
        }
        if (in_array($type, ['text', 'json'])) {
            $column->setLength(65535);
        }
        if (in_array($type, ['varchar'])) {
            $column->setLength(255);
        }
        if (in_array($type, ['boolean'])) {
            $column->setDefault($default);
        }
        if (in_array($type, [self::TYPE_DATETIME])) {
            $column->setDefault('CURRENT_TIMESTAMP');
        }
        if (StringHelper::endsWith($name, '_Order')) {
            $column->setDefault(0);
        }

        $column->setNotnull(!$nullable);

        if ($name === $table->getName() . '_ID') {
            $column->setAutoincrement(true);
            $this->addPrimaryKeyToTable($table, $name);
        }

        if (StringHelper::endsWith($name, '_Reference')) {
            $this->addIndexToTable($table, $name);
        }

        if (StringHelper::endsWith($name, '_Active')) {
            $this->addIndexToTable($table, $name);
        }

        if (StringHelper::endsWith($name, '_Order')) {
            $this->addIndexToTable($table, $name);
        }

        $exp = explode('_', $name);
        $foreignColumn = end($exp);
        $foreignTable = reset($exp);
        if ($table->getName() !== $foreignTable
            && $this->getSchema()->hasTable($foreignTable)
            && in_array($foreignColumn, ['ID', 'Code'])
        ) {
            $this->addForeignKeyToTable($table, $foreignTable, $name, null, $deleteCascade);
        }
        return $column;
    }

    protected function addPrimaryKeyToTable(Table $table, $key)
    {
        if (!is_array($key)) {
            $key = [$key];
        }
        if (!$table->hasPrimaryKey()) {
            $table->setPrimaryKey($key);
        }
    }

    protected function addUniqueKeyToTable(Table $table, $key)
    {
        if (!is_array($key)) {
            $key = [$key];
        }
        $table->addUniqueConstraint($key);
    }

    protected function addIndexToTable(Table $table, $key)
    {
        if (!is_array($key)) {
            $key = [$key];
        }
        try {
            $table->addIndex($key);
        } catch (SchemaException $schemaException) {
            $this->getParsContainer()->getLogger()->error($schemaException->getMessage());
        }
    }

    protected function addDefaultColumnsToTable(Table $table)
    {
        $this->addColumnToTable($table, 'Timestamp_Create', self::TYPE_DATETIME)
            ->setNotnull(false)->setDefault('CURRENT_TIMESTAMP');
        $this->addColumnToTable($table, 'Person_ID_Create', self::TYPE_INTEGER)
            ->setNotnull(false);
        $this->addColumnToTable($table, 'Timestamp_Edit', self::TYPE_DATETIME)
            ->setNotnull(false)->setDefault('CURRENT_TIMESTAMP');
        $this->addColumnToTable($table, 'Person_ID_Edit', self::TYPE_INTEGER)
            ->setNotnull(false);
        $this->addForeignKeyToTable($table, 'Person', 'Person_ID_Create', 'Person_ID');
        $this->addForeignKeyToTable($table, 'Person', 'Person_ID_Edit', 'Person_ID');
    }

    protected function addForeignKeyToTable(Table $table, string $foreignTable, string $localColumn, string $foreignColumn = null, bool $deleteCascade = false)
    {
        if (null === $foreignColumn) {
            $foreignColumn = $localColumn;
        }
        $options = [];
        if ($deleteCascade) {
            $options['onDelete'] = 'CASCADE';
        }
        $table->addForeignKeyConstraint($foreignTable, [$localColumn], [$foreignColumn], $options);
    }

    public function baseTables()
    {
        $schema = clone $this->getSchemaManager()->createSchema();

        $table = $this->getTableStatement('Person', $schema);
        $this->addColumnToTable($table, 'Person_ID', 'integer')
            ->setAutoincrement(true);
        $this->addColumnToTable($table, 'Person_Firstname', self::TYPE_STRING)
            ->setLength(255)->setNotnull(false);
        $this->addColumnToTable($table, 'Person_Lastname', self::TYPE_STRING)
            ->setLength(255)->setNotnull(false);
        $this->addPrimaryKeyToTable($table, 'Person_ID');
        $this->addDefaultColumnsToTable($table);

        $table = $this->getTableStatement('_DBVersion', $schema);
        $this->addColumnToTable($table, 'DBVersion_ID')
            ->setAutoincrement(true);
        $this->addPrimaryKeyToTable($table, 'DBVersion_ID');
        $this->addColumnToTable($table, 'DBVersion_Name', null, true);
        $this->addColumnToTable($table, 'DBVersion_Data', null, true);
        $this->addColumnToTable($table, 'DBVersion_Reference', null, true);
        $this->addDefaultColumnsToTable($table);

        $table = $this->getTableStatement('_DBTmp', $schema);
        $this->addColumnToTable($table, 'DBTmp_Code');
        $this->addPrimaryKeyToTable($table, 'DBTmp_Code');
        $this->addColumnToTable($table, 'DBTmp_Data', null, true);
        $this->addDefaultColumnsToTable($table);

        $table = $this->getTableStatement('_DBLock', $schema);
        $this->addColumnToTable($table, 'DBLock_ID')
            ->setAutoincrement(true);
        $this->addPrimaryKeyToTable($table, 'DBLock_ID');
        $this->addColumnToTable($table, 'DBLock_Reference', null, true);
        $this->addColumnToTable($table, 'Person_ID', null, true);
        $this->addDefaultColumnsToTable($table);

        $sql = $schema->getMigrateFromSql($this->getCurrentSchema(true), $this->getPlatform());
        return $this->query($sql);
    }

}
