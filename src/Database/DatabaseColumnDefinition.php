<?php

namespace Pars\Core\Database;

class DatabaseColumnDefinition implements \ArrayAccess
{

    /**
     * @var string|null
     */
    private ?string $column = null;

    /**
     * @var string|null
     */
    private ?string $table = null;

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
        return $this->column ?? $this->field;
    }

    /**
     * @param string $column
     * @return DatabaseColumnDefinition
     */
    public function setColumn(?string $column): self
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
     * @return DatabaseColumnDefinition
     */
    public function setTable(?string $table): self
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
     * @return DatabaseColumnDefinition
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
     * @return DatabaseColumnDefinition
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
        return $this->field ?? $this->column;
    }

    /**
     * @param string|null $field
     * @return DatabaseColumnDefinition
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
     * @return DatabaseColumnDefinition
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
        return isset($this->joinTableSelf);
    }

    /**
     * @param string|null $joinTableSelf
     * @return DatabaseColumnDefinition
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
     * @return DatabaseColumnDefinition
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

    public function offsetExists($offset)
    {
        return isset($this->{$offset});
    }

    public function offsetGet($offset)
    {
        return $this->{$offset};
    }

    public function offsetSet($offset, $value)
    {
        $this->{$offset} = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->{$offset});
    }
}
