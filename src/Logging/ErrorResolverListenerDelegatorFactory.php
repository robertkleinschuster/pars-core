<?php

namespace Pars\Core\Logging;

use Laminas\Stratigility\Middleware\ErrorHandler;
use Psr\Container\ContainerInterface;

/**
 * Class LoggingErrorListenerDelegatorFactory
 * @package Pars\Core\Logging
 */
class ErrorResolverListenerDelegatorFactory
{
    public function __invoke(ContainerInterface $container, string $name, callable $callback): ErrorHandler
    {
        $listener = new ErrorResolverListener($container);
        $errorHandler = $callback();
        $errorHandler->attachListener($listener);
        return $errorHandler;
    }
}
