<?php


namespace Pars\Core\Database;


use Laminas\Db\Sql\ExpressionInterface;

class DatabaseTableJoinDefinition
{
    /**
     * @var string
     */
    private string $table;
    /**
     * @var string
     */
    private string $type;
    /**
     * @var string|ExpressionInterface
     */
    private $on;

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @param string $table
     *
     * @return $this
     */
    public function setTable(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasTable(): bool
    {
        return isset($this->table);
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasType(): bool
    {
        return isset($this->type);
    }

    /**
     * @return string|ExpressionInterface
     */
    public function getOn()
    {
        return $this->on;
    }

    /**
     * @param string|ExpressionInterface $on
     *
     * @return $this
     */
    public function setOn($on): self
    {
        $this->on = $on;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasOn(): bool
    {
        return isset($this->on);
    }

}
