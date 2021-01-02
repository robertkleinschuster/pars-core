<?php

namespace Pars\Core\Database;

use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\AdapterAwareInterface;
use Laminas\Db\Adapter\AdapterAwareTrait;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Sql;
use Niceshops\Bean\Saver\AbstractBeanSaver;
use Niceshops\Bean\Type\Base\BeanInterface;

class DatabaseBeanSaver extends AbstractBeanSaver implements AdapterAwareInterface
{
    use AdapterAwareTrait;
    use DatabaseInfoTrait;

    /**
     * DatabaseBeanSaver constructor.
     * @param Adapter $adapter
     */
    public function __construct(Adapter $adapter)
    {
        $this->setDbAdapter($adapter);
    }

    /**
     * @param BeanInterface $bean
     * @return bool
     * @throws \Exception
     */
    protected function saveBean(BeanInterface $bean): bool
    {
        $result = [];
        $tableList = $this->getTable_List();
        foreach ($tableList as $table) {
            if ($this->beanExistsUnique($bean, $table)) {
                $result[] = $this->update($bean, $table);
            } else {
                $result[] = $this->insert($bean, $table);
            }
        }
        return !in_array(false, $result) && count($result) > 0;
    }

    /**
     * @param BeanInterface $bean
     * @return bool
     * @throws \Exception
     */
    protected function deleteBean(BeanInterface $bean): bool
    {
        $result_List = [];
        $tableList = $this->getTable_List();
        $tableList = array_reverse($tableList);
        foreach ($tableList as $table) {
            $deletedata = $this->getDataFromBean($bean, $table);
            // ensure only a single row is deleted
            if (count($deletedata) && $this->count($table, $deletedata) === 1) {
                $sql = new Sql($this->adapter);
                $delete = $sql->delete($table);
                $delete->where($deletedata);
                $result = $this->adapter->query($sql->buildSqlString($delete), $this->adapter::QUERY_MODE_EXECUTE);
                $result_List[] = $result->getAffectedRows() > 0;
            }
        }
        return !in_array(false, $result_List, true) && count($result_List) > 0;
    }


    /**
     * @param BeanInterface $bean
     * @param string $table
     * @return bool
     * @throws \Exception
     */
    protected function insert(BeanInterface $bean, string $table): bool
    {
        $insertdata = $this->getDataFromBean($bean, $table);
        if (count($insertdata)) {
            $sql = new Sql($this->adapter);
            $insert = $sql->insert($table);
            $insert->columns(array_keys($insertdata));
            $insert->values(array_values($insertdata));

            $result = $this->adapter->query($sql->buildSqlString($insert), $this->adapter::QUERY_MODE_EXECUTE);
            $keyField_List = $this->getKeyField_List($table, true);
            if (count($keyField_List) == 1) {
                foreach ($keyField_List as $field) {
                    $converter = new DatabaseBeanConverter();
                    $converter->convert($bean)->set($field, $result->getGeneratedValue());
                }
            }
            return $result->getAffectedRows() > 0;
        }

        return false;
    }


    /**
     * @param BeanInterface $bean
     * @param string $table
     * @return bool
     * @throws \Exception
     */
    protected function update(BeanInterface $bean, string $table): bool
    {
        $data = $this->getDataFromBean($bean, $table);
        // Ensure only a single row is changed
        if (count($data) && $this->beanExistsUnique($bean, $table)) {
            $sql = new Sql($this->adapter);
            $update = $sql->update($table);
            $keyFieldList = $this->getKeyField_List($table);
            foreach ($keyFieldList as $field) {
                if ($bean->isset($field)) {
                    $update->where([$this->getColumn($field) => $bean->get($field)]);
                }
            }
            $update->set($data);
            $result = $this->adapter->query($sql->buildSqlString($update), $this->adapter::QUERY_MODE_EXECUTE);
            return $result->getAffectedRows() > 0;
        }

        return false;
    }


    /**
     * @param BeanInterface $bean
     * @param string $table
     * @return bool
     * @throws \Exception
     */
    protected function beanExistsUnique(BeanInterface $bean, string $table): bool
    {
        $keyData = $this->getKeyDataFromBean($bean, $table);
        if (count($keyData)) {
            return $this->count($table, $keyData) === 1;
        } else {
            return false;
        }
    }

    /**
     * @param BeanInterface $bean
     * @param string|null $table
     * @param bool $includeKeys
     * @return array
     * @throws \Exception
     */
    protected function getDataFromBean(BeanInterface $bean, string $table = null, bool $includeKeys = true): array
    {
        $converter = new DatabaseBeanConverter();
        $data = [];
        $fieldList = $this->getField_List($table);
        foreach ($fieldList as $field) {
            if ($bean->initialized($field)) {
                $data[$this->getColumn($field)] = $converter->convert($bean)->get($field);
            }
        }
        if ($includeKeys) {
            $keyFieldList = $this->getKeyField_List($table);
            foreach ($keyFieldList as $field) {
                if ($bean->initialized($field)) {
                    $data[$this->getColumn($field)] = $converter->convert($bean)->get($field);
                }
            }
        }
        return $data;
    }

    /**
     * @param BeanInterface $bean
     * @param string $table
     * @throws \Exception
     */
    protected function getKeyDataFromBean(BeanInterface $bean, string $table): array
    {
        $formatter = new DatabaseBeanConverter();
        $data = [];
        $fieldList = $this->getKeyField_List($table);
        foreach ($fieldList as $field) {
            if ($bean->isset($field)) {
                $data[$this->getColumn($field)] = $formatter->convert($bean)->get($field);
            }
        }
        return $data;
    }

    /**
     * @param string $table
     * @param array $data
     * @return int|mixed
     */
    protected function count(string $table, array $data): int
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select($table);
        $select->where($data);
        $select->columns(['COUNT' => new Expression('COUNT(*)')], false);
        $result = $this->adapter->query($sql->buildSqlString($select), $this->adapter::QUERY_MODE_EXECUTE);
        return (int)($result->current()['COUNT'] ?? 0);
    }
}
