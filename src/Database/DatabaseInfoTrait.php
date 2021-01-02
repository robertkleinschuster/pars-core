<?php

namespace Pars\Core\Database;

trait DatabaseInfoTrait
{
    /**
     * @var array
     */
    private array $dbInfo_Map = [];

    private array $dbJoinInfo_Map = [];

    /**
     *
     * @param string $field
     * @param string $column
     * @param string $table
     * @param string $joinField
     * @param bool $isKey
     * @param string|null $joinFieldSelf
     * @param array $table_List
     * @param string|null $joinTableSelf
     * @return $this
     */
    public function addColumn(string $field, string $column, string $table, string $joinField, bool $isKey = false, string $joinFieldSelf = null, array $table_List = [], string $joinTableSelf = null)
    {
        if (null === $joinFieldSelf) {
            $joinFieldSelf = $joinField;
        }
        $this->dbInfo_Map[$field] = ['column' => $column, 'table' => $table, 'joinField' => $joinField, 'isKey' => $isKey, 'joinFieldSelf' => $joinFieldSelf, 'table_List' => $table_List, 'joinTableSelf' => $joinTableSelf];
        return $this;
    }

    public function resetDbInfo()
    {
        $this->dbInfo_Map = [];
        $this->dbJoinInfo_Map = [];
        return $this;
    }

    /**
     * @param string $table
     * @param string $type
     * @param $on
     */
    public function addJoinInfo(string $table, string $type, $on)
    {
        $this->dbJoinInfo_Map[$table] = ['type' => $type, 'on' => $on];
        return $this;
    }

    /**
     * @param string $table
     * @return bool
     */
    public function hasJoinInfo(string $table): bool
    {
        return isset($this->dbJoinInfo_Map[$table]);
    }

    /**
     * @param string $table
     * @return string
     */
    private function getJoinType(string $table): string
    {
        return $this->dbJoinInfo_Map[$table]['type'];
    }

    /**
     * @param string $table
     * @return mixed
     */
    private function getJoinOn(string $table)
    {
        return $this->dbJoinInfo_Map[$table]['on'];
    }

    /**
     * @param string|null $table
     * @return array
     */
    public function getField_List(string $table = null): array
    {
        if (null === $table) {
            return array_keys($this->dbInfo_Map);
        } else {
            return array_keys(array_filter($this->dbInfo_Map, function ($item) use ($table) {
                return $item['table'] === $table || in_array($table, $item['table_List']);
            }));
        }
    }

    /**
     * @param string $field
     * @return bool
     */
    private function hasField(string $field)
    {
        return isset($this->dbInfo_Map[$field]);
    }

    /**
     * @return array
     */
    private function getTable_List(): array
    {
        return array_unique(array_column($this->dbInfo_Map, 'table'));
    }

    /**
     * @param string $field
     * @return array
     * @throws \Exception
     */
    private function getTable(string $field): string
    {
        return $this->getInfo($field, 'table');
    }

    /**
     * @param string $field
     * @return string
     * @throws \Exception
     */
    private function getJoinField(string $field): string
    {
        return $this->getInfo($field, 'joinField');
    }

    /**
     * @param string $field
     * @return string
     * @throws \Exception
     */
    private function getJoinFieldSelf(string $field): string
    {
        return $this->getInfo($field, 'joinFieldSelf');
    }

    /**
     * @param string $field
     * @param string $default
     * @return string
     * @throws \Exception
     */
    private function getJoinTableSelf(string $field, string $default): string
    {
        return $this->getInfo($field, 'joinTableSelf', $default);
    }

    /**
     * @param string $field
     * @return mixed
     * @throws \Exception
     */
    private function getColumn(string $field): string
    {
        if (!isset($this->dbInfo_Map[$field])) {
            throw new \Exception('No column found for field ' . $field);
        }
        return $this->dbInfo_Map[$field]['column'];
    }

    /**
     * @param string $field
     * @param string $key
     * @param string|null $default
     * @return string
     * @throws \Exception
     */
    private function getInfo(string $field, string $key, string $default = null): string
    {
        if (!isset($this->dbInfo_Map[$field]) || $this->dbInfo_Map[$field] === null) {
            throw new \Exception("Field $field not found in db info.");
        }
        if (!isset($this->dbInfo_Map[$field][$key]) || $this->dbInfo_Map[$field][$key] === null) {
            if (null !== $default) {
                return $default;
            }
            throw new \Exception("Info $key in field $field not found in db info.");
        }
        return $this->dbInfo_Map[$field][$key];
    }

    /**
     * @param string|null $table
     * @param bool $primaryTable
     * @return array
     */
    private function getKeyField_List(?string $table = null, bool $primaryTable = false): array
    {
        return array_keys(array_filter($this->dbInfo_Map, function ($item) use ($table, $primaryTable) {
            if ($table !== null && $primaryTable) {
                return $item['isKey'] && ($item['table'] == $table);
            } elseif ($table !== null) {
                return $item['isKey'] && ($item['table'] == $table || in_array($table, $item['table_List']));
            } else {
                return $item['isKey'];
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
        foreach ($this->dbInfo_Map as $field => $item) {
            if ($item['column'] === $column) {
                return $field;
            }
        }
        throw new \Exception('No field found for column ' . $column);
    }
}
