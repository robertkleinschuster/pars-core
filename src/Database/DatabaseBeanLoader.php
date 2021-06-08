<?php

namespace Pars\Core\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;
use Pars\Bean\Finder\BeanFinderInterface;
use Pars\Bean\Finder\FilterExpression;
use Pars\Bean\Finder\FilterIdentifier;
use Pars\Bean\Loader\AbstractBeanLoader;
use Pars\Bean\Type\Base\BeanInterface;
use Pars\Pattern\Exception\DatabaseException;

class DatabaseBeanLoader extends AbstractBeanLoader implements ParsDatabaseAdapterAwareInterface
{
    use DatabaseInfoTrait;
    use ParsDatabaseAdapterAwareTrait;

    /**
     * @var string[]
     */
    private $where_Map;

    /**
     * @var string[]
     */
    private $exclude_Map;

    /**
     * @var FilterExpression[]
     */
    private $expression_List;

    /**
     * @var array[]
     */
    private $like_Map;

    /**
     * @var array[]
     */
    private $order_Map;

    /**
     * @var Result
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
     * @var
     */
    private $customColumn_Map;

    private $lock = false;

    /**
     * UserBeanLoader constructor.
     * @param ParsDatabaseAdapter $adapter
     */
    public function __construct(ParsDatabaseAdapter $adapter)
    {
        $this->setDatabaseAdapter($adapter);
        $this->where_Map = [];
        $this->exclude_Map = [];
        $this->like_Map = [];
        $this->order_Map = [];
        $this->customColumn_Map = [];
        $this->expression_List = [];
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
        $this->customColumn_Map = [];
        $this->limit = null;
        $this->offset = null;
        $this->result = null;
        $this->expression_List = [];
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
    public function filterValue($field, $value = null, $logic = BeanFinderInterface::FILTER_MODE_AND)
    {
        if ($this->hasField($field)) {
            $this->where_Map[$logic][$field] = $value;
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
    public function unsetValue($field, $value = null, $logic = BeanFinderInterface::FILTER_MODE_AND)
    {
        if ($this->hasField($field)) {
            unset($this->where_Map[$logic][$field]);
        }
        return $this;
    }


    /**
     * @param string $field
     * @param $value
     * @param string $logic
     * @throws \Exception
     */
    public function excludeValue(string $field, $value, $logic = BeanFinderInterface::FILTER_MODE_AND)
    {
        if ($this->hasField($field)) {
            $this->exclude_Map[$logic][$field] = $value;
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
    public function addLike(string $str, $fields, $mode = BeanFinderInterface::FILTER_MODE_AND)
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
        if ($this->hasField($field)) {
            $this->order_Map[$this->buildColumn($field)] = $desc ? 'DESC' : 'ASC';
        } else {
            $this->order_Map[$field] = $desc ? 'DESC' : 'ASC';
        }
    }

    /**
     * @param string $field
     * @return string
     * @throws \Exception
     */
    protected function buildColumn(string $field): string
    {
        return "{$this->getTable($field)}.{$this->getColumn($field)}";
    }


    protected function handleJoins(QueryBuilder $builder)
    {
        $self = $builder->getQueryPart('from');
        $self = reset($self)['table'];
        foreach ($this->getField_List() as $field) {
            $table = $this->getTable($field);
            if ($table !== $self) {
                $joins = $builder->getQueryPart('join');
                $addedTables = [];
                if (isset($joins[$self])) {
                    $addedTables = array_column($joins[$self], 'joinTable');
                }
                if (!in_array($table, $addedTables)) {
                    $column = $this->getColumn($this->getJoinField($field));
                    $columnSelf = $this->getColumn($this->getJoinFieldSelf($field));
                    $tableSelf = $this->getJoinTableSelf($field, $self);
                    if ($this->hasJoinInfo($table)) {
                        $type = $this->getJoinType($table);
                        $condition = $this->getJoinOn($table);
                        $exp = $builder->expr()->and($builder->expr()->eq("$tableSelf.$columnSelf", "$table.$column"));
                        if (is_array($condition)) {
                            foreach ($condition as $key => $value) {

                                $exp = $exp->with($builder->expr()->eq($this->buildColumn($field), $builder->createNamedParameter($value,
                                    $this->getValueParameterType($value), $this->buildPlaceholder($key, 'join', $value))));
                            }
                        }
                        $builder->{"{$type}Join"}($self, $table, $table, $exp);
                    } else {
                        $builder->join($self, $table, $table, "$tableSelf.$columnSelf = $table.$column");
                    }
                }
            }
        }
    }


    protected function handleWhere(QueryBuilder $builder)
    {
        foreach ($this->exclude_Map as $logic => $map) {
            foreach ($map as $field => $value) {
                if (is_array($value)) {
                    $where = $builder->expr()->notIn(
                        $this->buildColumn($field),
                        $builder->createNamedParameter($value)
                    );
                } elseif ($value === null) {
                    $where = $builder->expr()->isNotNull($this->buildColumn($field));
                } else {
                    $where = $builder->expr()->neq(
                        "{$this->getTable($field)}.{$this->getColumn($field)}",
                        $builder->createNamedParameter($value,
                            $this->getValueParameterType($value),
                            $this->buildPlaceholder($field, 'exclude', $value)
                        )
                    );
                }
                switch ($logic) {
                    case BeanFinderInterface::FILTER_MODE_OR:
                        $builder->orWhere($where);
                        break;
                    case BeanFinderInterface::FILTER_MODE_AND:
                        $builder->andWhere($where);
                        break;
                }

            }
        }

        foreach ($this->where_Map as $logic => $map) {
            foreach ($map as $field => $value) {
                if (is_array($value)) {
                    $where = $builder->expr()->in(
                        $this->buildColumn($field),
                        $builder->createNamedParameter($value, Connection::PARAM_STR_ARRAY, $this->buildPlaceholder($field, 'filter_in'))
                    );
                } elseif ($value === null) {
                    $where = $builder->expr()->isNull($this->buildColumn($field));
                } else {
                    $where = $builder->expr()->eq(
                        $this->buildColumn($field),
                        $builder->createNamedParameter($value,
                            $this->getValueParameterType($value),
                            $this->buildPlaceholder($field, 'filter', $value)
                        )
                    );
                }
                switch ($logic) {
                    case BeanFinderInterface::FILTER_MODE_OR:
                        $builder->orWhere($where);
                        break;
                    case BeanFinderInterface::FILTER_MODE_AND:
                        $builder->andWhere($where);
                        break;
                }

            }
        }


        foreach ($this->expression_List as $logic => $expressions) {
            foreach ($expressions as $expression) {
                /**
                 * @var FilterExpression $expression
                 */
                $left = $expression->getLeft();
                $right = $expression->getRight();
                if ($left instanceof FilterIdentifier) {
                    $left = $left->getIdentifier();
                } elseif ($left instanceof \DateTime) {
                    $left = $left->format(DatabaseBeanConverter::DATE_FORMAT);
                } else {
                    $left = $builder->createNamedParameter($left, $this->getValueParameterType($left));
                }
                if ($right instanceof FilterIdentifier) {
                    $right = $right->getIdentifier();
                } elseif ($right instanceof \DateTime) {
                    $right = $right->format(DatabaseBeanConverter::DATE_FORMAT);
                    $right = $builder->createNamedParameter($right, $this->getValueParameterType($right));
                } else {
                    $right = $builder->createNamedParameter($right, $this->getValueParameterType($right));
                }
                switch ($expression->getOperator()) {
                    case FilterExpression::OPERATOR_EQUAL:
                        $where = $builder->expr()->eq($left, $right);
                        break;
                    case FilterExpression::OPERATOR_NOT_EQUAL:
                        $where = $builder->expr()->neq($left, $right);
                        break;
                    case FilterExpression::OPERATOR_GREATER_THAN:
                        $where = $builder->expr()->gt($left, $right);
                        break;
                    default:
                        $where = $builder->expr()->eq($left, $right);
                }
                switch ($logic) {
                    case BeanFinderInterface::FILTER_MODE_OR:
                        $builder->orWhere($where);
                        break;
                    case BeanFinderInterface::FILTER_MODE_AND:
                        $builder->andWhere($where);
                        break;
                }

            }
        }
    }


    protected function handleLimit(QueryBuilder $builder)
    {
        if ($this->hasLimit()) {
            $builder->setMaxResults($this->getLimit());
        }
        if ($this->hasOffset()) {
            $builder->setFirstResult($this->getOffset());
        }
        return $this;
    }


    protected function handleLike(QueryBuilder $builder)
    {
        foreach ($this->like_Map as $str => $like) {
            $fields = $like['fields'];
            $mode = $like['mode'];
            if (!is_array($fields)) {
                $fields = [$fields];
            }
            foreach ($fields as $field) {
                $column = $this->buildColumn($field);
                $where = $builder->expr()->like($column, $builder->createNamedParameter($str, $this->getValueParameterType($str), $this->buildPlaceholder($field, 'search')));
                if ($mode == BeanFinderInterface::FILTER_MODE_OR) {
                    $builder->orWhere($where);
                } else {
                    $builder->andWhere($where);
                }
            }
        }

        return $this;
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return $this
     */
    public function limit(int $limit, int $offset = 0)
    {
        $this->setLimit($limit);
        $this->setOffset($offset);
        return $this;
    }


    public function count(): int
    {
        $builder = $this->buildQuery();
        $builder->select('COUNT(*) AS COUNT');
        return $builder->executeQuery()->fetchOne();
    }

    protected function init(): int
    {
        return $this->getResult()->rowCount();
    }

    protected function load(): ?array
    {
        if ($this->result === null) {
            throw new DatabaseException('Could not fetch data. Run find first.');
        }
        $ret = $this->result->fetchAssociative();
        if ($ret) {
            return $ret;
        }
        return null;
    }

    protected function getResult()
    {
        if (null === $this->result) {
            $builder = $this->buildQuery(true, true);
            $this->result = $builder->executeQuery();
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
            $beanData[$field] = $data[$this->buildColumn($field)];
        }
        foreach ($this->customColumn_Map as $alias => $column) {
            $beanData[$alias] = $data[$alias];
        }
        return $converter->convert($bean, $beanData)->toBean();
    }

    public function buildQuery(bool $limit = false, bool $selectColumns = false)
    {

        $builder = $this->getDatabaseAdapter()->getConnection()->createQueryBuilder();
        $table_List = $this->getTable_List();
        $builder->from(reset($table_List));
        $this->handleJoins($builder);
        $this->handleWhere($builder);
        $this->handleLike($builder);
        $this->handleOrder($builder);
        if ($limit) {
            $this->handleLimit($builder);
        }
        if ($selectColumns) {
            $this->handleSelect($builder);
        }

        return $builder;
    }


    protected function handleOrder(QueryBuilder $builder)
    {
        foreach ($this->order_Map as $field => $order) {
            if (isset($this->customColumn_Map[$field])) {
                $column = $this->customColumn_Map[$field];
                $builder->addOrderBy("($column)", $order);
            } else {
                $builder->addOrderBy($field, $order);
            }
        }
    }


    protected function handleSelect(QueryBuilder $builder)
    {
        $columns = [];
        foreach ($this->getField_List() as $field) {
            $alias = $this->getDatabaseAdapter()->getConnection()->quote($this->buildColumn($field));
            $column = $this->getDatabaseAdapter()->getConnection()->quoteIdentifier($this->buildColumn($field));
            $columns[] = "$column AS $alias";
        }
        foreach ($this->customColumn_Map as $alias => $column) {
            $columns[] = "($column) AS $alias";
        }
        $builder->select(...$columns);
    }

    /**
     * @param $column
     * @return $this
     */
    public function addCustomColumn($column, $alias)
    {
        $this->customColumn_Map[$alias] = $column;
        return $this;
    }


    public function preloadValueList(string $field): array
    {
        $builder = $this->buildQuery(true, false);
        $builder->select($this->buildColumn($field));
        return $builder->fetchFirstColumn();
    }

    public function search(string $search, array $field_List = null)
    {
        if (null === $field_List) {
            $field_List = $this->getField_List();
        }
        $this->addLike("%$search%", $field_List, BeanFinderInterface::FILTER_MODE_OR);
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
            if ($values instanceof FilterExpression) {
                $this->filterExpression($values, $mode);
            } else {
                $this->filterValue($field, $values, $mode);
            }
        }
    }

    protected function filterExpression(FilterExpression $expression, string $mode)
    {
        $this->expression_List[$mode][] = $expression;
        return $this;
    }

    public function exclude(array $data_Map)
    {
        foreach ($data_Map as $field => $value) {
            $this->excludeValue($field, $value);
        }
    }

    public function lock()
    {
        $this->lock = true;
        return $this;
    }


}
