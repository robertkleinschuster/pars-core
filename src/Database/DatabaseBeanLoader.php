<?php

namespace Pars\Core\Database;

use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\AdapterAwareInterface;
use Laminas\Db\Adapter\AdapterAwareTrait;
use Laminas\Db\Adapter\Driver\StatementInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Predicate\Like;
use Laminas\Db\Sql\Predicate\Predicate;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;
use Niceshops\Bean\Finder\BeanFinderInterface;
use Niceshops\Bean\Loader\AbstractBeanLoader;
use Niceshops\Bean\Type\Base\BeanInterface;
use Niceshops\Core\Exception\DatabaseException;

class DatabaseBeanLoader extends AbstractBeanLoader implements AdapterAwareInterface
{
    use AdapterAwareTrait;
    use DatabaseInfoTrait;


    /**
     * @var string[]
     */
    private $where_Map;

    /**
     * @var string[]
     */
    private $exclude_Map;

    /**
     * @var array[]
     */
    private $like_Map;

    /**
     * @var array[]
     */
    private $order_Map;

    /**
     * @var ResultSet
     */
    private $result = null;

    /**
     * @var int
     */
    private $limit;

    /**
     * @var
     */
    private $offset;


    /**
     * UserBeanLoader constructor.
     * @param Adapter $adapter
     * @param string $table
     */
    public function __construct(Adapter $adapter)
    {
        $this->setDbAdapter($adapter);
        $this->where_Map = [];
        $this->exclude_Map = [];
        $this->like_Map = [];
        $this->order_Map = [];
    }

    /**
     * @param string $table
     */
    public function addDefaultFields(string $table)
    {
        $this->addField('Person_ID_Create')->setTable($table);
        $this->addField('Person_ID_Edit')->setTable($table);
        $this->addField('Timestamp_Create')->setTable($table);
        $this->addField('Timestamp_Edit')->setTable($table);
    }

    /**
     * @return $this
     */
    public function reset(): self
    {
        $this->where_Map = [];
        $this->exclude_Map = [];
        $this->like_Map = [];
        $this->order_Map = [];
        $this->limit = null;
        $this->offset = null;
        $this->result = null;
        return $this;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     *
     * @return $this
     */
    public function setLimit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasLimit(): bool
    {
        return $this->limit !== null;
    }

    /**
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * @param int $offset
     *
     * @return $this
     */
    public function setOffset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasOffset(): bool
    {
        return $this->offset !== null;
    }

    /**
     * @param string $field
     * @param array $valueList
     * @return DatabaseBeanLoader|void
     * @throws \Exception
     */
    public function initByValueList(string $field, array $valueList)
    {
        return $this->filterValue($field, $valueList);
    }

    /**
     * @param string $field
     * @param $value
     * @param string $logic
     * @return DatabaseBeanLoader
     * @throws \Exception
     */
    public function filterValue($field, $value = null, $logic = Predicate::OP_AND)
    {
        if ($field instanceof Predicate) {
            $this->where_Map[$logic][] = $field;
        } else {
            if ($this->hasField($field)) {
                $this->where_Map[$logic]["{$this->getTable($field)}.{$this->getColumn($field)}"] = $value;
            }
        }

        return $this;
    }

    /**
     * @param string $field
     * @param $value
     * @param string $logic
     * @return DatabaseBeanLoader
     * @throws \Exception
     */
    public function unsetValue($field, $value = null, $logic = Predicate::OP_AND)
    {
        if ($this->hasField($field)) {
            unset($this->where_Map[$logic]["{$this->getTable($field)}.{$this->getColumn($field)}"]);
        }
        return $this;
    }


    /**
     * @param string $field
     * @param $value
     * @param string $logic
     * @throws \Exception
     */
    public function excludeValue(string $field, $value, $logic = Predicate::OP_AND)
    {
        if ($this->hasField($field)) {
            $this->exclude_Map[$logic]["{$this->getTable($field)}.{$this->getColumn($field)}"] = $value;
        }
    }


    /**
     * @param array $idMap
     * @throws \Exception
     */
    public function initByIdMap(array $idMap)
    {
        foreach ($idMap as $field => $value) {
            if (!empty($value)) {
                $this->filterValue($field, $value);
            }
        }
    }

    /**
     * @param string $str
     * @param string|array $fields
     * @param string $mode
     * @return $this
     */
    public function addLike(string $str, $fields, $mode = Predicate::OP_AND)
    {
        $this->like_Map[$str] = [
            'fields' => $fields,
            'mode' => $mode
        ];
        return $this;
    }

    /**
     * @param string $field
     * @param bool $desc
     */
    public function addOrder(string $field, bool $desc = false)
    {
        $this->order_Map[$field] = $desc ? 'DESC' : 'ASC';
    }

    /**
     * @param Select $select
     * @throws \Exception
     */
    protected function handleJoins(Select $select)
    {
        $self = $select->getRawState(Select::TABLE);
        foreach ($this->getField_List() as $field) {
            $table = $this->getTable($field);
            if ($table !== $self) {
                $joins = $select->getRawState(Select::JOINS);
                if (!in_array($table, array_column($joins->getJoins(), 'name'))) {
                    $column = $this->getColumn($this->getJoinField($field));
                    $columnSelf = $this->getColumn($this->getJoinFieldSelf($field));
                    $tableSelf = $this->getJoinTableSelf($field, $self);
                    if ($this->hasJoinInfo($table)) {
                        $select->join($table, $this->getJoinOn($table), [], $this->getJoinType($table));
                    } else {
                        $select->join($table, "$tableSelf.$columnSelf = $table.$column", []);
                    }
                }
            }
        }
    }

    /**
     * @param Select $select
     */
    protected function handleWhere(Select $select)
    {
        foreach ($this->exclude_Map as $logic => $map) {
            foreach ($map as $column => $value) {
                $where = new Predicate();
                $where->notEqualTo($column, $value);
                $select->where($where);
            }
        }

        foreach ($this->where_Map as $logic => $map) {
            $select->where($map, $logic);
        }
    }

    /**
     * @param Select $select
     * @return DatabaseBeanLoader
     */
    protected function handleLimit(Select $select)
    {
        if ($this->hasLimit()) {
            $select->limit($this->getLimit());
        }
        if ($this->hasOffset()) {
            $select->offset($this->getOffset());
        }
        return $this;
    }

    /**
     * @param Select $select
     * @return DatabaseBeanLoader
     */
    protected function handleLike(Select $select)
    {
        $likePredicate = null;
        if ($likePredicate == null) {
            $likePredicate = new Predicate();
        }
        foreach ($this->like_Map as $str => $like) {
            if (is_array($like['fields'])) {
                $predicate = new Predicate();
                foreach ($like['fields'] as $field) {
                    $predicate->addPredicate(new Like("{$this->getTable($field)}.{$this->getColumn($field)}", $str), $like['mode']);
                }
                $likePredicate->addPredicate($predicate, $like['mode']);
            } elseif (is_string($like['fields'])) {
                $likePredicate->addPredicate(new Like("{$this->getTable($like['fields'])}.{$this->getColumn($like['fields'])}", $str), $like['mode']);
            }
        }
        if ($likePredicate->count()) {
            $select->where($likePredicate);
        }
        return $this;
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return $this
     */
    public function limit(int $limit, int $offset)
    {
        $this->setLimit($limit);
        $this->setOffset($offset);
        return $this;
    }


    /**
     * @return int
     */
    public function count(): int
    {
        $select = $this->buildSelect();
        $select->columns(['COUNT' => new Expression('COUNT(*)')], false);
        $result = $this->getPreparedStatement($select)->execute();
        return $result->current()['COUNT'] ?? 0;
    }

    protected function init(): int
    {
        return $this->getResult()->count();
    }

    protected function load(): ?array
    {
        if ($this->result === null) {
            throw new DatabaseException('Could not fetch data. Run find first.');
        }
        if ($this->result->key() < $this->result->count() - 1) {
            $ret = $this->result->current();
            $this->result->next();
            return $ret;
        }
        return null;
    }


    /**
     * @param bool $limit
     * @return \Laminas\Db\Adapter\Driver\ResultInterface|ResultSet
     */
    protected function getResult()
    {
        if (null === $this->result) {
            $this->result = $this->getPreparedStatement($this->buildSelect(true, true))->execute();
        }
        return $this->result;
    }

    /**
     * @param BeanInterface $bean
     * @param array $data
     * @return BeanInterface
     * @throws \Exception
     */
    public function initializeBeanWithData(BeanInterface $bean, array $data): BeanInterface
    {
        $converter = new DatabaseBeanConverter();
        $beanData = [];
        foreach ($this->getField_List() as $field) {
            $beanData[$field] = $data["{$this->getTable($field)}.{$this->getColumn($field)}"];
        };
        return $converter->convert($bean, $beanData)->toBean();
    }


    /**
     * @param bool $limit
     * @param bool $selectColumns
     * @return Select
     */
    protected function buildSelect(bool $limit = false, bool $selectColumns = false): Select
    {
        $sql = new Sql($this->adapter);
        $table_List = $this->getTable_List();
        $select = $sql->select(reset($table_List));
        $this->handleJoins($select);
        $this->handleWhere($select);
        $this->handleLike($select);
        $this->handleOrder($select);
        if ($limit) {
            $this->handleLimit($select);
        }
        if ($selectColumns) {
            $this->handleSelect($select);
        }

        return $select;
    }

    /**
     * @param Select $select
     * @throws \Exception
     */
    protected function handleOrder(Select $select)
    {
        foreach ($this->order_Map as $field => $order) {
            $select->order("{$this->getTable($field)}.{$this->getColumn($field)} $order");
        }
    }

    /**
     * @param $select
     * @throws \Exception
     */
    protected function handleSelect(Select $select)
    {
        $columns = [];
        foreach ($this->getField_List() as $field) {
            $columns[] = "{$this->getTable($field)}.{$this->getColumn($field)}";
        }
        $select->columns($columns, false);
    }

    /**
     * @param Select $select
     * @return \Laminas\Db\Adapter\Driver\ResultInterface|StatementInterface|\Laminas\Db\ResultSet\ResultSet|\Laminas\Db\ResultSet\ResultSetInterface|null
     */
    protected function getPreparedStatement(Select $select)
    {
        return $this->adapter->query((new Sql($this->adapter))->buildSqlString($select));
    }

    /**
     * @param string $field
     * @return array
     * @throws \Exception
     */
    public function preloadValueList(string $field): array
    {
        $select = $this->buildSelect(true, false);
        $select->reset(Select::COLUMNS);
        $column = $this->getColumn($field);
        $table = $this->getTable($field);
        $tableColumn = "$table.$column";
        $select->columns([$tableColumn => $tableColumn], false);
        $result = $this->getPreparedStatement($select)->execute();
        $ret = [];
        foreach ($result as $row) {
            $ret[] = $row[$tableColumn];
        }
        return $ret;
    }

    /**
     * @return string
     */
    public function getLastQuery(): string
    {
        return $this->adapter->getProfiler()->getLastProfile()['sql'];
    }

    public function search(string $search, array $field_List = null)
    {
        if (null === $field_List) {
            $field_List = $this->getField_List();
        }
        $this->addLike("%$search%", $field_List, Predicate::OP_OR);
        return $this;
    }

    public function order(array $field_List)
    {
        foreach ($field_List as $field => $mode) {
            if ($mode == BeanFinderInterface::ORDER_MODE_ASC) {
                $this->addOrder($field);
            } elseif ($mode == BeanFinderInterface::ORDER_MODE_DESC) {
                $this->addOrder($field, true);
            } else {
                $this->addOrder($mode);
            }
        }
    }

    public function filter(array $data_Map, string $mode)
    {
        foreach ($data_Map as $field => $values) {
            if ($mode == BeanFinderInterface::FILTER_MODE_AND) {
                $this->filterValue($field, $values);
            }
            if ($mode == BeanFinderInterface::FILTER_MODE_OR) {
                $this->filterValue($field, $values, Predicate::OP_OR);
            }
        }
    }

    public function exclude(array $data_Map)
    {
        foreach ($data_Map as $field => $value) {
            $this->excludeValue($field, $value);
        }
    }
}
