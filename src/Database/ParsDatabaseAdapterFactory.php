<?php


namespace Pars\Core\Database;


use Doctrine\DBAL\DriverManager;
use Pars\Pattern\Exception\CoreException;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class ParsDatabaseAdapterFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        if (!isset($config['db'])) {
            throw new CoreException('Database config missing.');
        }
        $connectionParams = array(
            'dbname' => $config['db']['database'],
            'user' =>  $config['db']['username'],
            'password' => $config['db']['password'],
            'host' => $config['db']['hostname'],
            'driver' => strtolower($config['db']['driver']),
        );
        return new ParsDatabaseAdapter(DriverManager::getConnection($connectionParams), $container->get(LoggerInterface::class));
    }

}
