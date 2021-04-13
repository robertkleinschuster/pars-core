<?php

namespace Pars\Core\Logging;

use Laminas\Stratigility\Middleware\ErrorHandler;
use Psr\Container\ContainerInterface;

/**
 * Class LoggingErrorListenerDelegatorFactory
 * @package Pars\Core\Logging
 */
class LoggingErrorListenerDelegatorFactory
{
    public function __invoke(ContainerInterface $container, string $name, callable $callback): ErrorHandler
    {
        $listener = new LoggingErrorListener($container->get(\Psr\Log\LoggerInterface::class));
        $errorHandler = $callback();
        $errorHandler->attachListener($listener);
        return $errorHandler;
    }
}
