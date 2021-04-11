<?php

namespace Pars\Core\Logging;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
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
        #CacheClearer::executeConfigCacheFunction($this->container->get('config'));
        #UpdateHandler::handleAppUpdate($this->container);
    }
}
