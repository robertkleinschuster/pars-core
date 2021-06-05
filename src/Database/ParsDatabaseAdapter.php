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

    public function startTransaction()
    {
        try {
            $this->getConnection()->beginTransaction();
        } catch (\Throwable $exception) {
            $this->logError($exception->getMessage(), ['exception' => $exception]);
        }
    }


    public function commitTransaction()
    {
        try {
            $this->getConnection()->commit();
        } catch (\Throwable $exception) {
            $this->logError($exception->getMessage(), ['exception' => $exception]);
        }
    }

    public function rollbackTransaction()
    {
        try {
            $this->getConnection()->rollBack();
        } catch (\Throwable $exception) {
            $this->logError($exception->getMessage(), ['exception' => $exception]);
        }
    }
}
