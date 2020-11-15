<?php

namespace Pars\Core\Database;

class DatabaseColumnDefinition
{

    /**
     * @var string
     */
    private string $column;

    /**
     * @var string
     */
    private string $table;

    /**
     * @var string[]
     */
    private array $additionalTable_List = [];

    /**
     * @var string|null
     */
    private ?string $joinField = null;

    /**
     * @var string|null
     */
    private ?string $field = null;

    /**
     * @var string|null
     */
    private ?string $joinFieldSelf = null;

    /**
     * @var string|null
     */
    private ?string $joinTableSelf = null;

    /**
     * @var bool
     */
    private bool $key = false;

    /**
     * @return string
     */
    public function getColumn(): string
    {
        return $this->column;
    }

    /**
     * @param string $column
     */
    public function setColumn(string $column): self
    {
        $this->column = $column;
        return $this;
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @param string $table
     */
    public function setTable(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getAdditionalTableList(): array
    {
        return $this->additionalTable_List;
    }

    /**
     * @param string[] $additionalTable_List
     */
    public function setAdditionalTableList(array $additionalTable_List): self
    {
        $this->additionalTable_List = $additionalTable_List;
        return $this;
    }

    /**
     * @return string
     */
    public function getJoinField(): string
    {
        return $this->joinField ?? $this->getField();
    }

    /**
     * @param string|null $joinField
     */
    public function setJoinField(?string $joinField): self
    {
        $this->joinField = $joinField;
        return $this;
    }

    /**
     * @return string
     */
    public function getField(): string
    {
        return $this->field ?? $this->getColumn();
    }

    /**
     * @param string|null $field
     */
    public function setField(?string $field): self
    {
        $this->field = $field;
        return $this;
    }

    /**
     * @return string
     */
    public function getJoinFieldSelf(): string
    {
        return $this->joinFieldSelf ?? $this->getJoinField();
    }

    /**
     * @param string|null $joinFieldSelf
     */
    public function setJoinFieldSelf(?string $joinFieldSelf): self
    {
        $this->joinFieldSelf = $joinFieldSelf;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getJoinTableSelf(): ?string
    {
        return $this->joinTableSelf;
    }

    /**
     * @return bool
     */
    public function hasJoinTableSelf(): bool
    {
        return null !== $this->joinTableSelf;
    }

    /**
     * @param string|null $joinTableSelf
     */
    public function setJoinTableSelf(?string $joinTableSelf): self
    {
        $this->joinTableSelf = $joinTableSelf;
        return $this;
    }

    /**
     * @return bool
     */
    public function isKey(): bool
    {
        return $this->key;
    }

    /**
     * @param bool $key
     */
    public function setKey(bool $key): self
    {
        $this->key = $key;
        return $this;
    }


    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'column' => $this->getColumn(),
            'table' => $this->getTable(),
            'joinField' => $this->getJoinField(),
            'isKey' => $this->isKey(),
            'joinFieldSelf' => $this->getJoinFieldSelf(),
            'table_List' => $this->getAdditionalTableList(),
            'joinTableSelf' => $this->getJoinTableSelf()
        ];
    }

    /**
     * @param array $definition
     * @return $this
     */
    public function fromArray(array $definition): self
    {
        return $this
            ->setColumn($definition['column'])
            ->setTable($definition['table'])
            ->setJoinField($definition['joinField'])
            ->setKey($definition['isKey'])
            ->setJoinFieldSelf($definition['joinFieldSelf'])
            ->setAdditionalTableList($definition['table_List'])
            ->setJoinTableSelf($definition['joinTableSelf']);
    }
}
