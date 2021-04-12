<?php


namespace Pars\Core\Database;


use Pars\Pattern\Exception\CoreException;

trait ParsDatabaseAdapterAwareTrait
{
    protected ParsDatabaseAdapter $databaseAdapter;

    /**
    * @return ParsDatabaseAdapter
    */
    public function getDatabaseAdapter(): ParsDatabaseAdapter
    {
        if (!$this->hasDatabaseAdapter()) {
            throw new CoreException('No database adapter set');
        }
        return $this->databaseAdapter;
    }

    /**
    * @param ParsDatabaseAdapter $databaseAdapter
    *
    * @return $this
    */
    public function setDatabaseAdapter(ParsDatabaseAdapter $databaseAdapter): self
    {
        $this->databaseAdapter = $databaseAdapter;
        return $this;
    }

    /**
    * @return bool
    */
    public function hasDatabaseAdapter(): bool
    {
        return isset($this->databaseAdapter);
    }

}
