<?php

namespace Pars\Core\Database;


use Pars\Bean\Saver\AbstractBeanSaver;
use Pars\Bean\Type\Base\BeanInterface;
use Pars\Pattern\Exception\CoreException;

class DatabaseBeanSaver extends AbstractBeanSaver implements ParsDatabaseAdapterAwareInterface
{
    use DatabaseInfoTrait;
    use ParsDatabaseAdapterAwareTrait;


    public function __construct(ParsDatabaseAdapter $adapter)
    {
        $this->setDatabaseAdapter($adapter);
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
     * @param BeanInterface $bean
     * @return bool
     * @throws \Exception
     */
    protected function saveBean(BeanInterface $bean): bool
    {
        $result_List = [];
        $this->getDatabaseAdapter()->transactionBegin();;
        $tableList = $this->getTable_List();
        foreach ($tableList as $table) {
            if ($this->beanExistsUnique($bean, $table)) {
                $result_List[] = $this->update($bean, $table);
            } else {
                $result_List[] = $this->insert($bean, $table);
            }
        }
        $result = !in_array(false, $result_List) && count($result_List) > 0;
        if ($result) {
            $this->getDatabaseAdapter()->transactionCommit();;
        } else {
            $this->getDatabaseAdapter()->transactionRollback();;
        }
        return $result;
    }

    /**
     * @param BeanInterface $bean
     * @return bool
     * @throws \Exception
     */
    protected function deleteBean(BeanInterface $bean): bool
    {
        $result_List = [];
        $this->getDatabaseAdapter()->transactionBegin();;
        $tableList = $this->getTable_List();
        $tableList = array_reverse($tableList);
        foreach ($tableList as $table) {
            $deletedata = $this->getDataFromBean($bean, $table, true, true);
            // ensure only a single row is deleted
            if (count($deletedata) && $this->count($table, $deletedata) === 1) {
                $builder = $this->getDatabaseAdapter()->getQueryBuilder();
                $delete = $builder->delete($table);
                foreach ($deletedata as $key => $value) {
                    $delete->andWhere($builder->expr()->eq($key, $builder->createNamedParameter($value, $this->getValueParameterType($value))));
                }
                $result_List[] = $delete->executeStatement();
            }
        }
        $result = !in_array(false, $result_List, true) && count($result_List) > 0;
        if ($result) {
            $this->getDatabaseAdapter()->transactionCommit();
        } else {
            $this->getDatabaseAdapter()->transactionRollback();
        }
        return $result;
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
            $builder = $this->getDatabaseAdapter()->getQueryBuilder();
            $insert = $builder->insert($table);
            foreach ($insertdata as &$value) {
                $value = $insert->createNamedParameter($value, $this->getValueParameterType($value));
            }
            $insert->values($insertdata);


            $result = $insert->executeStatement();
            $keyField_List = $this->getKeyField_List($table, true);
            if (count($keyField_List) == 1) {
                foreach ($keyField_List as $field) {
                    $converter = new DatabaseBeanConverter();
                    $converter->convert($bean)->set($field, $this->getDatabaseAdapter()->getConnection()->lastInsertId());
                }
            }
            return $result;
        }
        return count($insertdata) == 0;
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
            $builder = $this->getDatabaseAdapter()->getQueryBuilder();
            $update = $builder->update($table);
            $keyFieldList = $this->getKeyField_List($table);
            foreach ($keyFieldList as $field) {
                if ($bean->isset($field)) {
                    $value = $bean->get($field);
                    $update->andWhere($builder->expr()->eq($this->getColumn($field),
                    $builder->createNamedParameter($value, $this->getValueParameterType($value))));
                }
            }
            foreach ($data as $key => $value) {
                $update->set($key, $builder->createNamedParameter($value, $this->getValueParameterType($value)));
            }
            return $update->executeStatement();
        }
        return count($data) == 0;
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
    protected function getDataFromBean(BeanInterface $bean, string $table = null, bool $includeKeys = true, bool $onlyKeys = false): array
    {
        $converter = new DatabaseBeanConverter();
        $data = [];
        if (!$onlyKeys) {
            $fieldList = $this->getField_List($table);
            foreach ($fieldList as $field) {
                if ($bean->initialized($field)) {
                    $data[$this->getColumn($field)] = $converter->convert($bean)->get($field);
                }
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
     * @throws CoreException
     * @throws \Doctrine\DBAL\Exception
     */
    protected function count(string $table, array $data): int
    {
        $builder = $this->getDatabaseAdapter()->getQueryBuilder();
        $select = $builder->select('COUNT(*) AS COUNT');
        $select->from($table);
        foreach ($data as $key => $value) {
            $select->andWhere($builder->expr()->eq($key, $builder->createNamedParameter($value, $this->getValueParameterType($value))));
        }
        return (int)($builder->executeQuery()->fetchOne() ?? 0);
    }
}
