<?php


namespace Pars\Core\Database;


use Doctrine\DBAL\Connection;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

class ParsDatabaseAdapter
{
    use LoggerAwareTrait;
    protected Connection $connection;


    /**
     * ParsDbAdapter constructor.
     */
    public function __construct(Connection $connection, LoggerInterface $logger)
    {
        $this->connection = $connection;
        $this->setLogger($logger);
    }

    /**
     * @return Connection
     */
    public function getConnection(): Connection
    {
        return $this->connection;
    }

    /**
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->getConnection()->createQueryBuilder();
    }

    /**
     * @return \Doctrine\DBAL\Schema\AbstractSchemaManager
     * @throws \Doctrine\DBAL\Exception
     */
    public function getSchemaManager()
    {
        return $this->getConnection()->createSchemaManager();
    }

    /**
     * @return \Doctrine\DBAL\Query\Expression\ExpressionBuilder
     */
    public function getExpressionBuilder()
    {
        return $this->getConnection()->createExpressionBuilder();
    }

    public function getDebug()
    {
        $result = [];

        return $result;
    }

    /**
     * @param string $message
     * @param array $data
     */
    protected function logError(string $message, array $data)
    {
        if (isset($this->logger)) {
            $this->logger->error($message, $data);
        }
    }

    /**
     * @param string $message
     * @param array $data
     */
    protected function logWarning(string $message, array $data)
    {
        if (isset($this->logger)) {
            $this->logger->warning($message, $data);
        }
    }

    public function transactionBegin()
    {
        try {
            $this->getConnection()->beginTransaction();
        } catch (\Throwable $exception) {
            $this->logError($exception->getMessage(), ['exception' => $exception]);
        }
    }


    public function transactionCommit()
    {
        try {
            $this->getConnection()->commit();
        } catch (\Throwable $exception) {
            $this->logError($exception->getMessage(), ['exception' => $exception]);
        }
    }

    public function transactionRollback()
    {
        try {
            $this->getConnection()->rollBack();
        } catch (\Throwable $exception) {
            $this->logError($exception->getMessage(), ['exception' => $exception]);
        }
    }

    /**
     * @return DatabaseLock
     */
    public function getLock(): DatabaseLock
    {
        return new DatabaseLock($this);
    }

    /**
     * @return DatabaseTemp
     */
    public function getTemp(): DatabaseTemp
    {
        return new DatabaseTemp($this);
    }
}
