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

abstract class AbstractUpdater implements ValidationHelperAwareInterface, AdapterAwareInterface
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
            if ($constraint->getName() == $constraintName) {
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
            return $ex->getMessage();
        }
    }

    protected function query(AbstractSql $statement)
    {
        $sql = new Sql($this->adapter);
        $result = '';
        if ($this->isExecute()) {
            $result = $this->adapter->query(
                $sql->buildSqlString($statement, $this->adapter),
                Adapter::QUERY_MODE_EXECUTE
            );
        }
        if ($this->isPreview()) {
            $result = str_replace(PHP_EOL, '<br>', $sql->buildSqlString($statement, $this->adapter));
        }
        return $result;
    }

    /**
     * @param string $table
     * @param string $column
     * @return array
     */
    protected function getKeyList(string $table, string $column)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select($table);
        $select->columns([$column]);
        $result = $this->adapter->query(
            $sql->buildSqlString($select, $this->adapter),
            Adapter::QUERY_MODE_EXECUTE
        );
        return array_column($result->toArray(), $column);
    }

    /**
     * @param string $table
     * @param string $keyColumn
     * @param array $data_Map
     * @param bool $noUpdate
     * @param array $forceUpdateColumns
     * @return array
     */
    protected function saveDataMap(string $table, string $keyColumn, array $data_Map, bool $noUpdate = false, array $forceUpdateColumns = [])
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
            if (in_array($item[$keyColumn], $existingKey_List)) {
                if (!$noUpdate) {
                    $update = $sql->update($table);
                    $update->where([$keyColumn => $item[$keyColumn]]);
                    unset($item[$keyColumn]);
                    $update->set($item);
                    $result[] = $this->query($update);
                } elseif (count(array_intersect(array_keys($item), $forceUpdateColumns))) {
                    $update = $sql->update($table);
                    $update->where([$keyColumn => $item[$keyColumn]]);
                    unset($item[$keyColumn]);
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
        $newKey_List = array_column($data_Map, $keyColumn);
        foreach ($existingKey_List as $id) {
            if (!in_array($id, $newKey_List)) {
                $delete = $sql->delete($table);
                $delete->where([$keyColumn => $id]);
                $result[] = $this->query($delete);
            }
        }
        return $result;
    }

    /**
     * @param string $table
     * @return AlterTable|CreateTable
     */
    protected function getTableStatement(string $tableName)
    {
        if (!in_array($tableName, $this->existingTableList)) {
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
        $this->addConstraintToTable($table, new ForeignKey(null, 'Person_ID_Create', 'Person', 'Person_ID'));
        $this->addConstraintToTable($table, new ForeignKey(null, 'Person_ID_Edit', 'Person', 'Person_ID'));
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
        $constraintName_old = $this->abbreviate($type, 2) . '' . $this->abbreviate($tableName, 2) . '' . $this->abbreviate(implode('', $constraint->getColumns()), 2);

        if ($table instanceof CreateTable || $constraint instanceof ForeignKey) {
            $arrDropped = [];

            if ($this->hasConstraints($tableName, $constraintName)) {
                $table->dropConstraint($constraintName);
                $arrDropped[] = $constraintName;
                $constraint->setName($constraintName . '_');
                $table->addConstraint($constraint);
            } elseif ($this->hasConstraints($tableName, $constraintName . '_')) {
                $table->dropConstraint($constraintName . '_');
                $arrDropped[] = $constraintName . '_';
                $constraint->setName($constraintName);
                $table->addConstraint($constraint);
            } else {
                $constraint->setName($constraintName);
                $table->addConstraint($constraint);
            }

            if (!in_array($constraintName_old, $arrDropped)
                && $this->hasConstraints($tableName, $constraintName_old)) {
                $table->dropConstraint($constraintName_old);
            }
            if (!in_array($constraintName_old . '_', $arrDropped)
                && $this->hasConstraints($tableName, $constraintName_old . '_')) {
                $table->dropConstraint($constraintName_old . '_');
            }

        }
    }

    protected function abbreviate($string, $l = 2)
    {
        $results = ''; // empty string
        $vowels = array('a', 'e', 'i', 'o', 'u', 'y'); // vowels
        preg_match_all('/[A-Z][a-z]*/', ucfirst($string), $m); // Match every word that begins with a capital letter, added ucfirst() in case there is no uppercase letter
        foreach ($m[0] as $substring) {
            $substring = str_replace($vowels, '', $substring); // String to lower case and remove all vowels
            $results .= preg_replace('/([a-z]{' . $l . '})(.*)/', '$1', $substring); // Extract the first N letters.
        }
        return $results;
    }
}
