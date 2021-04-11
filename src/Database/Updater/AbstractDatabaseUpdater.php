<?php

namespace Pars\Core\Database\Updater;

use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\AdapterAwareInterface;
use Laminas\Db\Adapter\AdapterAwareTrait;
use Laminas\Db\Sql\AbstractSql;
use Laminas\Db\Sql\Ddl\AlterTable;
use Laminas\Db\Sql\Ddl\Column\Column;
use Laminas\Db\Sql\Ddl\Column\Integer;
use Laminas\Db\Sql\Ddl\Column\Timestamp;
use Laminas\Db\Sql\Ddl\Constraint\AbstractConstraint;
use Laminas\Db\Sql\Ddl\Constraint\ForeignKey;
use Laminas\Db\Sql\Ddl\Constraint\PrimaryKey;
use Laminas\Db\Sql\Ddl\CreateTable;
use Laminas\Db\Sql\Sql;
use Pars\Helper\Validation\ValidationHelperAwareInterface;
use Pars\Helper\Validation\ValidationHelperAwareTrait;

abstract class AbstractDatabaseUpdater implements ValidationHelperAwareInterface, AdapterAwareInterface
{
    use ValidationHelperAwareTrait;
    use AdapterAwareTrait;

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
     * SchemaUpdater constructor.
     * @param $adapter
     */
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->metadata = \Laminas\Db\Metadata\Source\Factory::createSourceFromAdapter($this->adapter);
        $this->existingTableList = $this->metadata->getTableNames($adapter->getCurrentSchema());
    }

    /**
     * @param string $table
     * @param string $constraintName
     * @return bool
     */
    public function hasConstraints(string $table, string $constraintName)
    {
        foreach ($this->metadata->getConstraints($table, $this->adapter->getCurrentSchema()) as $constraint) {
            if (trim(strtolower($constraint->getName())) == trim(strtolower($constraintName))) {
                return true;
            }
        }
        return false;
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
        $this->adapter->getDriver()->getConnection()->beginTransaction();
        $this->setMode(self::MODE_EXECUTE);
        $methodList = $this->getUpdateMethodList();
        $resultMap = [];
        foreach ($methods as $method => $enabled) {
            if (boolval($enabled) && in_array($method, $methodList)) {
                $resultMap[$method] = $this->executeMethod($method);
            }
        }
        $this->adapter->getDriver()->getConnection()->commit();
        return $resultMap;
    }

    public function executeSilent()
    {
        $this->adapter->getDriver()->getConnection()->beginTransaction();
        $this->setMode(self::MODE_EXECUTE);
        $methodList = $this->getUpdateMethodList();
        $resultMap = [];
        foreach ($methodList as $method) {
            $resultMap[$method] = $this->executeMethod($method);
        }
        $this->adapter->getDriver()->getConnection()->commit();
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
            return $ex->getMessage();
        }
    }

    protected function query(AbstractSql $statement)
    {
        $sql = new Sql($this->adapter);
        $result = '';
        $query = $sql->buildSqlString($statement, $this->adapter);
        if ($this->isExecute()) {
            $result = $this->adapter->query($query, Adapter::QUERY_MODE_EXECUTE);
        }
        if ($this->isPreview()) {
            $result = str_replace(PHP_EOL, '<br>', $query);
        }
        return $result;
    }

    /**
     * @param string $table
     * @param string $column
     * @return array
     */
    protected function getKeyList(string $table, $column)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select($table);
        $col = $column;
        if (!is_array($column)) {
            $column = [$column];
        }
        $select->columns($column);
        $dbresult = $this->adapter->query(
            $sql->buildSqlString($select, $this->adapter),
            Adapter::QUERY_MODE_EXECUTE
        );
        $result = [];
        if (is_array($col)) {
            $dbdata = $dbresult->toArray();
            foreach ($col as $item) {
                $result[$item] = array_column($dbdata, $item);
            }
        } else {
            $result = array_column($dbresult->toArray(), $col);
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
            $sql = new Sql($this->adapter);
            if ($this->isUpdate($item, $keyColumn, $existingKey_List)) {
                if (!$noUpdate) {
                    $update = $sql->update($table);
                    if (is_array($keyColumn)) {
                        foreach ($keyColumn as $column) {
                            $update->where([$column => $item[$column]]);
                            unset($item[$column]);
                        }
                    } else {
                        $update->where([$keyColumn => $item[$keyColumn]]);
                        unset($item[$keyColumn]);
                    }
                    $update->set($item);
                    $result[] = $this->query($update);
                } elseif (count(array_intersect(array_keys($item), $forceUpdateColumns))) {
                    $update = $sql->update($table);
                    if (is_array($keyColumn)) {
                        foreach ($keyColumn as $column) {
                            $update->where([$column => $item[$column]]);
                            unset($item[$column]);
                        }
                    } else {
                        $update->where([$keyColumn => $item[$keyColumn]]);
                        unset($item[$keyColumn]);
                    }
                    $data = [];
                    foreach ($forceUpdateColumns as $forceUpdateColumn) {
                        if (isset($item[$forceUpdateColumn])) {
                            $data[$forceUpdateColumn] = $item[$forceUpdateColumn];
                        }
                    }
                    $update->set($data);
                    $result[] = $this->query($update);
                }
            } else {
                $insert = $sql->insert($table);
                $insert->columns(array_keys($item));
                $insert->values(array_values($item));
                $result[] = $this->query($insert);
            }
        }
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
                    $delete = $sql->delete($table);
                    $delete->where($item);
                    $result[] = $this->query($delete);
                }
            }
        } else {
            foreach ($existingKey_List as $id) {
                $newKey_List = array_column($data_Map, $keyColumn);
                if (!in_array($id, $newKey_List)) {
                    $delete = $sql->delete($table);
                    $delete->where([$keyColumn => $id]);
                    $result[] = $this->query($delete);
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

    /**
     * @param string $table
     * @return AlterTable|CreateTable
     */
    protected function getTableStatement(string $tableName, bool $forceUpdate = false)
    {
        if (!in_array($tableName, $this->existingTableList) && !$forceUpdate) {
            $table = new CreateTable($tableName);
        } else {
            $table = new AlterTable($tableName);
        }
        return $table;
    }

    protected function addDefaultColumnsToTable(AbstractSql $table)
    {
        $this->addColumnToTable($table, new Timestamp('Timestamp_Create', true));
        $this->addColumnToTable($table, new Integer('Person_ID_Create', true));
        $this->addColumnToTable($table, new Timestamp('Timestamp_Edit', true));
        $this->addColumnToTable($table, new Integer('Person_ID_Edit', true));
    }

    protected function addDefaultConstraintsToTable(AbstractSql $table)
    {
        $this->addConstraintToTable($table, new ForeignKey(null, 'Person_ID_Create', 'Person', 'Person_ID'));
        $this->addConstraintToTable($table, new ForeignKey(null, 'Person_ID_Edit', 'Person', 'Person_ID'));
    }

    protected function dropDefaultConstraintsFromTable(AbstractSql $table)
    {
        $this->dropConstraintFromTable($table, new ForeignKey(null, 'Person_ID_Create', 'Person', 'Person_ID'));
        $this->dropConstraintFromTable($table, new ForeignKey(null, 'Person_ID_Edit', 'Person', 'Person_ID'));
    }


    /**
     * @param AbstractSql $table
     * @param Column $column
     * @return Column
     * @throws \Exception
     */
    protected function addColumnToTable(AbstractSql $table, Column $column)
    {
        if ($table instanceof CreateTable) {
            $table->addColumn($column);
        }
        if ($table instanceof AlterTable) {
            $columns = $this->metadata->getColumnNames((string)$table->getRawState(AlterTable::TABLE), $this->adapter->getCurrentSchema());
            if (!in_array($column->getName(), $columns)) {
                $table->addColumn($column);
            } else {
                $table->changeColumn($column->getName(), $column);
            }
        }

        return $column;
    }

    /**
     * @param AbstractSql $table
     * @param Column $column
     * @return Column
     * @throws \Exception
     */
    protected function dropColumnFromTable(AbstractSql $table, Column $column)
    {
        if ($table instanceof AlterTable) {
            $columns = $this->metadata->getColumnNames((string)$table->getRawState(AlterTable::TABLE), $this->adapter->getCurrentSchema());
            if (in_array($column->getName(), $columns)) {
                $table->dropColumn($column->getName(), $column);
            }
        }
        return $column;
    }

    /**
     * @param AbstractSql $table
     * @param AbstractConstraint $constraint
     */
    protected function addConstraintToTable(AbstractSql $table, AbstractConstraint $constraint)
    {
        $tableName = (string)$table->getRawState(AlterTable::TABLE);
        $path = explode('\\', get_class($constraint));
        $type = array_pop($path);
        if ($constraint instanceof PrimaryKey) {
            $constraintName = "_laminas_{$tableName}_PRIMARY";
        } else {
            $constraintName = $this->abbreviate($type, 2) . '' . $this->abbreviate($tableName, 4) . '' . $this->abbreviate(implode('', $constraint->getColumns()), 4);
        }

        if ($table instanceof CreateTable || $constraint instanceof ForeignKey) {
            $constraint->setName($constraintName);
            $table->addConstraint($constraint);
        }
    }


    /**
     * @param AbstractSql $table
     * @param AbstractConstraint $constraint
     */
    protected function dropConstraintFromTable(AbstractSql $table, AbstractConstraint $constraint)
    {
        $tableName = (string)$table->getRawState(AlterTable::TABLE);
        $path = explode('\\', get_class($constraint));
        $type = array_pop($path);
        if ($constraint instanceof PrimaryKey) {
            $constraintName = "_laminas_{$tableName}_PRIMARY";
        } else {
            $constraintName = $this->abbreviate($type, 2) . '' . $this->abbreviate($tableName, 4) . '' . $this->abbreviate(implode('', $constraint->getColumns()), 4);
        }
        $constraintName_old = $this->abbreviate($type, 2) . '' . $this->abbreviate($tableName, 2) . '' . $this->abbreviate(implode('', $constraint->getColumns()), 2);

        if ($table instanceof CreateTable || $constraint instanceof ForeignKey) {
            $arrDropped = [];

            if ($this->hasConstraints($tableName, $constraintName)) {
                $table->dropConstraint($constraintName);
                $arrDropped[] = $constraintName;
            }

            if (
                !in_array($constraintName_old, $arrDropped)
                && $this->hasConstraints($tableName, $constraintName_old)
            ) {
                $table->dropConstraint($constraintName_old);
            }

            if (
                !in_array($constraintName_old . '_', $arrDropped)
                && $this->hasConstraints($tableName, $constraintName_old . '_')
            ) {
                $table->dropConstraint($constraintName_old . '_');
            }

            if ($this->hasConstraints($tableName, $constraintName . '_')) {
                $table->dropConstraint($constraintName . '_');
            }
        }
    }


    protected function abbreviate($string, $l = 2)
    {
        $results = ''; // empty string
        $vowels = ['a', 'e', 'i', 'o', 'u', 'y']; // vowels
        preg_match_all('/[A-Z][a-z]*/', ucfirst($string), $m); // Match every word that begins with a capital letter, added ucfirst() in case there is no uppercase letter
        foreach ($m[0] as $substring) {
            $substring = str_replace($vowels, '', $substring); // String to lower case and remove all vowels
            $results .= preg_replace('/([a-z]{' . $l . '})(.*)/', '$1', $substring); // Extract the first N letters.
        }
        return $results;
    }
}
