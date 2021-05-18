<?php


namespace Pars\Core\Deployment;


use Psr\Container\ContainerInterface;

/**
 * Interface UpdaterInterface
 * @package Pars\Core\Deployment
 */
interface UpdaterInterface
{
    /**
     * UpdaterInterface constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container);

    /**
     * @return mixed
     */
    public function update();

    public function updateDB();
}
