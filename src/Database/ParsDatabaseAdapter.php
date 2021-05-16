<?php


namespace Pars\Core\Database;


use Laminas\Db\Adapter\AdapterAwareInterface;
use Laminas\Db\Adapter\AdapterAwareTrait;
use Laminas\Db\Adapter\AdapterInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

class ParsDatabaseAdapter implements AdapterAwareInterface
{
    use AdapterAwareTrait;
    use LoggerAwareTrait;

    /**
     * ParsDbAdapter constructor.
     */
    public function __construct(AdapterInterface $adapter, LoggerInterface $logger = null)
    {
        $this->setDbAdapter($adapter);
        if ($logger) {
            $this->setLogger($logger);
        }
    }

    /**
     * @return AdapterInterface
     */
    public function getDbAdapter(): AdapterInterface
    {
        return $this->adapter;
    }

    protected function logError(string $message, array $data)
    {
        if (isset($this->logger)) {
            $this->logger->error($message, $data);
        }
    }

    protected function logWarning(string $message, array $data)
    {
        if (isset($this->logger)) {
            $this->logger->warning($message, $data);
        }
    }

    public function startTransaction()
    {
        try {
            $this->getDbAdapter()->getDriver()->getConnection()->beginTransaction();
        } catch (\Throwable $exception) {
            $this->logError($exception->getMessage(), ['exception' => $exception]);
        }
    }


    public function commitTransaction()
    {
        try {
            $this->getDbAdapter()->getDriver()->getConnection()->commit();
        } catch (\Throwable $exception) {
            $this->logError($exception->getMessage(), ['exception' => $exception]);
        }
    }

    public function rollbackTransaction()
    {
        try {
            $this->getDbAdapter()->getDriver()->getConnection()->commit();
        } catch (\Throwable $exception) {
            $this->logError($exception->getMessage(), ['exception' => $exception]);
        }
    }
}
