<?php

namespace Pars\Core\Database;

use Doctrine\DBAL\ParameterType;


class DatabaseLock implements ParsDatabaseAdapterAwareInterface
{
    use ParsDatabaseAdapterAwareTrait;

    public function __construct(ParsDatabaseAdapter $parsDatabaseAdapter)
    {
        $this->setDatabaseAdapter($parsDatabaseAdapter);
    }

    /**
     * @param int $person_ID
     * @param string $reference
     * @return int
     * @throws \Doctrine\DBAL\Exception
     * @throws \Pars\Pattern\Exception\CoreException
     */
    public function lock(int $person_ID, string $reference)
    {
        $builder = $this->getDatabaseAdapter()->getQueryBuilder();
        $builder->insert('_DBLock');
        $builder->values([
            'Person_ID' => $builder->createNamedParameter($person_ID, ParameterType::INTEGER),
            'DBLock_Reference' => $builder->createNamedParameter($reference, ParameterType::STRING),
        ]);
        return $builder->executeStatement();
    }

    /**
     * @param int $person_ID
     * @return int
     * @throws \Doctrine\DBAL\Exception
     * @throws \Pars\Pattern\Exception\CoreException
     */
    public function release(int $person_ID)
    {
        $builder = $this->getDatabaseAdapter()->getQueryBuilder();
        $builder->select('COUNT(*)');
        $builder->from('_DBLock');
        $builder->andWhere($builder->expr()->eq('Person_ID', $builder->createNamedParameter($person_ID, ParameterType::INTEGER)));
        $count = $builder->fetchOne();
        if ($count) {
            $builder = $this->getDatabaseAdapter()->getQueryBuilder();
            $builder->delete('_DBLock');
            $builder->andWhere($builder->expr()->eq('Person_ID', $builder->createNamedParameter($person_ID, ParameterType::INTEGER)));
            return $builder->executeStatement();
        }
        return $count;
    }

    /**
     * @param string $reference
     * @return false|mixed
     * @throws \Doctrine\DBAL\Exception
     * @throws \Pars\Pattern\Exception\CoreException
     */
    public function has(string $reference)
    {
        $builder = $this->getDatabaseAdapter()->getQueryBuilder();
        $builder->select('Person_ID');
        $builder->from('_DBLock');
        $builder->andWhere($builder->expr()->eq('DBLock_Reference', $builder->createNamedParameter($reference, ParameterType::STRING)));
        $date = (new \DateTime())->modify("-1 day");
        $builder->andWhere($builder->expr()->gt('Timestamp_Create', $builder->createNamedParameter($date->format(DatabaseBeanConverter::DATE_FORMAT))));
        return $builder->fetchOne();
    }

}
