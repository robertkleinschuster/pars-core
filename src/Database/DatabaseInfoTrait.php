<?php

namespace Pars\Core\Database;

use Doctrine\DBAL\ParameterType;

trait DatabaseInfoTrait
{
    /**
     * @var DatabaseColumnDefinition[]
     */
    private array $dbColumnDefinition_Map = [];

    /**
     * @var DatabaseTableJoinDefinition[]
     */
    private array $dbTableJoinDefinition_Map = [];

    /**
     * @deprecated
     * @see DatabaseInfoTrait::addField
     *
     * @param string $field
     * @param string|null $column
     * @param string|null $table
     * @param string|null $joinField
     * @param bool $isKey
     * @param string|null $joinFieldSelf
     * @param array $table_List
     * @param string|null $joinTableSelf
     * @return DatabaseColumnDefinition
     */
    public function addColumn(
        string $field,
        string $column = null,
        string $table = null,
        string $joinField = null,
        bool $isKey = false,
        string $joinFieldSelf = null,
        array $table_List = [],
        string $joinTableSelf = null
    ): DatabaseColumnDefinition {
        if (null === $joinFieldSelf) {
            $joinFieldSelf = $joinField;
        }
        $definitionMap = [
            'column' => $column,
            'table' => $table,
            'joinField' => $joinField,
            'isKey' => $isKey,
            'joinFieldSelf' => $joinFieldSelf,
            'table_List' => $table_List,
            'joinTableSelf' => $joinTableSelf
        ];
        $columnDefinition = new DatabaseColumnDefinition();
        $columnDefinition->setField($field);
        $columnDefinition->fromArray($definitionMap);
        $this->dbColumnDefinition_Map[$field] = $columnDefinition;
        return $columnDefinition;
    }

    /**
     * @param string $field
     * @return DatabaseColumnDefinition
     */
    public function addField(string $field): DatabaseColumnDefinition
    {
        $exp = explode('.', $field);
        $columnDefinition = new DatabaseColumnDefinition();
        if (count($exp) === 2) {
            $columnDefinition->setTable($exp[0]);
            $columnDefinition->setField($exp[1]);
            $field = $columnDefinition->getField();
        } else {
            $columnDefinition->setField($field);
        }
        $this->dbColumnDefinition_Map[$field] = $columnDefinition;
        return $columnDefinition;
    }

    /**
     * @param string $table
     */
    public function addDefaultFields(string $table): self
    {
        $this->addField('Person_ID_Create')->setTable($table);
        $this->addField('Person_ID_Edit')->setTable($table);
        $this->addField('Timestamp_Create')->setTable($table);
        $this->addField('Timestamp_Edit')->setTable($table);
        return $this;
    }

    /**
     * @return $this
     */
    public function resetDbInfo()
    {
        $this->dbColumnDefinition_Map = [];
        $this->dbTableJoinDefinition_Map = [];
        return $this;
    }

    /**
     * @param string $table
     * @param string $type
     * @param array $on
     * @return DatabaseTableJoinDefinition
     */
    public function addJoinInfo(string $table, string $type, $on = null): DatabaseTableJoinDefinition
    {
        $joinInfo = (new DatabaseTableJoinDefinition())->setTable($table)->setType($type)->setOn($on);
        $this->dbTableJoinDefinition_Map[$table] = $joinInfo;
        return $joinInfo;
    }

    /**
     * @param string $table
     * @return bool
     */
    public function hasJoinInfo(string $table): bool
    {
        return isset($this->dbTableJoinDefinition_Map[$table]);
    }

    /**
     * @param string $table
     * @return string
     */
    private function getJoinType(string $table): string
    {
        return $this->dbTableJoinDefinition_Map[$table]->getType();
    }

    /**
     * @param string $table
     * @return mixed
     */
    private function getJoinOn(string $table)
    {
        return $this->dbTableJoinDefinition_Map[$table]->getOn();
    }

    /**
     * @param string|null $table
     * @return array
     */
    public function getField_List(string $table = null): array
    {
        if (null === $table) {
            return array_keys($this->dbColumnDefinition_Map);
        } else {
            return array_keys(array_filter($this->dbColumnDefinition_Map, function ($item) use ($table) {
                return $item->getTable() === $table || in_array($table, $item->getAdditionalTableList());
            }));
        }
    }

    /**
     * @param string $field
     * @return bool
     */
    private function hasField(string $field)
    {
        return isset($this->dbColumnDefinition_Map[$field]);
    }

    /**
     * @return array
     */
    private function getTable_List(): array
    {
        $table_List = [];
        foreach ($this->dbColumnDefinition_Map as $item) {
            if ($item->hasTable() && !in_array($item->getTable(), $table_List)) {
                $table_List[] = $item->getTable();
            }
        }
        return $table_List;
    }

    /**
     * @param string $field
     * @return string
     * @throws \Exception
     */
    private function getTable(string $field): ?string
    {
        return $this->getDefinition($field)->getTable();
    }

    /**
     * @param string $field
     * @return string
     * @throws \Exception
     */
    private function getJoinField(string $field): string
    {
        return $this->getDefinition($field)->getJoinField();
    }

    /**
     * @param string $field
     * @return string
     * @throws \Exception
     */
    private function getJoinFieldSelf(string $field): string
    {
        return $this->getDefinition($field)->getJoinFieldSelf();
    }

    /**
     * @param string $field
     * @param string $default
     * @return string
     * @throws \Exception
     */
    private function getJoinTableSelf(string $field, string $default): string
    {
        return $this->getDefinition($field)->hasJoinTableSelf()
            ? $this->getDefinition($field)->getJoinTableSelf() : $default;
    }

    /**
     * @param string $field
     * @return mixed
     * @throws \Exception
     */
    private function getColumn(string $field): string
    {
        return $this->getDefinition($field)->getColumn();
    }

    /**
     * @param string $field
     * @return DatabaseColumnDefinition
     * @throws \Exception
     */
    private function getDefinition(string $field): DatabaseColumnDefinition
    {
        if (!isset($this->dbColumnDefinition_Map[$field])) {
            throw new \Exception("Field $field not found in db info.");
        }
        return $this->dbColumnDefinition_Map[$field];
    }

    /**
     * @param string|null $table
     * @param bool $primaryTable
     * @return array
     */
    private function getKeyField_List(?string $table = null, bool $primaryTable = false): array
    {
        return array_keys(array_filter($this->dbColumnDefinition_Map, function ($item) use ($table, $primaryTable) {
            if ($table !== null && $primaryTable) {
                return $item->isKey() && ($item->getTable() == $table);
            } elseif ($table !== null) {
                return $item->isKey()
                    && ($item->getTable() == $table || in_array($table, $item->getAdditionalTableList()));
            } else {
                return $item->isKey();
            }
        }));
    }


    /**
     * @param string $column
     * @return int|string
     * @throws \Exception
     */
    private function getField(string $column): string
    {
        foreach ($this->dbColumnDefinition_Map as $field => $item) {
            if ($item->getColumn() === $column) {
                return $field;
            }
        }
        throw new \Exception('No field found for column ' . $column);
    }

    protected function buildPlaceholder(string $field, string $prefix, $value = null): string
    {
        static $count = null;
        if ($count === null) {
            $count = 0;
        }
        $count++;
        $field = strtolower($field);
        $prefix = strtolower($prefix);
        return ":{$prefix}_{$field}_{$count}";
    }

    protected function getValueParameterType($value)
    {
        if (null === $value) {
            return ParameterType::NULL;
        }
        switch (gettype($value)) {
            case 'integer':
                return ParameterType::INTEGER;
            case 'boolean':
                return ParameterType::BOOLEAN;
            default:
                return ParameterType::STRING;
        }
    }

}
