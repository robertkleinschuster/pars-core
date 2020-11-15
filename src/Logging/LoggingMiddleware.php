<?php

namespace Pars\Core\Logging;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class LoggingMiddleware
 * @package Pars\Core\Logging
 */
class LoggingMiddleware implements MiddlewareInterface
{

    public const LOGGER_ATTRIBUTE = 'logger';

    private LoggerInterface $logger;

    /**
     * LoggingMiddleware constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $handler->handle($request->withAttribute(self::LOGGER_ATTRIBUTE, $this->logger));
    }
}
