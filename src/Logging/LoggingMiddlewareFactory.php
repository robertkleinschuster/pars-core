<?php

namespace Pars\Core\Logging;

use Psr\Container\ContainerInterface;

/**
 * Class LoggingMiddlewareFactory
 * @package Pars\Core\Logging
 */
class LoggingMiddlewareFactory
{

    /**
     * @param ContainerInterface $container
     * @return LoggingMiddleware
     */
    public function __invoke(ContainerInterface $container)
    {
        return new LoggingMiddleware($container->get('Logger'));
    }
}
