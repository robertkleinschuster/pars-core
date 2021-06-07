<?php


namespace Pars\Core\Database;


use Doctrine\DBAL\ParameterType;

class DatabaseTemp implements ParsDatabaseAdapterAwareInterface
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
    public function set(string $code, $data)
    {
        $data = json_encode($data);
        $builder = $this->getDatabaseAdapter()->getQueryBuilder();
        $builder->insert('_DBTmp');
        $builder->values([
            'DBTmp_Code' => $builder->createNamedParameter($code, ParameterType::STRING),
            'DBTmp_Data' => $builder->createNamedParameter($data, ParameterType::STRING),
        ]);
        return $builder->executeStatement();
    }

    /**
     * @param int $person_ID
     * @return int
     * @throws \Doctrine\DBAL\Exception
     * @throws \Pars\Pattern\Exception\CoreException
     */
    public function delete(string $code)
    {
        $builder = $this->getDatabaseAdapter()->getQueryBuilder();
        $builder->select('COUNT(*)');
        $builder->from('_DBTmp');
        $builder->andWhere($builder->expr()->eq('DBTmp_Code', $builder->createNamedParameter($code, ParameterType::STRING)));
        $count = $builder->fetchOne();
        if ($count) {
            $builder = $this->getDatabaseAdapter()->getQueryBuilder();
            $builder->delete('_DBTmp');
            $builder->andWhere($builder->expr()->eq('DBTmp_Code', $builder->createNamedParameter($code, ParameterType::STRING)));
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
    public function get(string $code)
    {
        $builder = $this->getDatabaseAdapter()->getQueryBuilder();
        $builder->select('DBTmp_Data');
        $builder->from('_DBTmp');
        $builder->andWhere($builder->expr()->eq('DBTmp_Code', $builder->createNamedParameter($code, ParameterType::STRING)));
        return json_decode($builder->fetchOne());
    }

}
