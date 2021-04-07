<?php

namespace Pars\Core\Logging;

use Pars\Core\Deployment\UpdateHandler;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Class LoggingErrorListener
 * @package Pars\Core\Logging
 */
class ErrorResolverListener
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Throwable $error, ServerRequestInterface $request, ResponseInterface $response)
    {
        UpdateHandler::handleAppUpdate($this->container);
    }
}
