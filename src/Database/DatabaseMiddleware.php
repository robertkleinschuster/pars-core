<?php

declare(strict_types=1);

namespace Pars\Core\Database;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class DatabaseMiddleware
 * @package Pars\Core\Database
 */
class DatabaseMiddleware implements MiddlewareInterface
{

    public const ADAPTER_ATTRIBUTE = 'db_adapter';


    /**
     * DatabaseMiddleware constructor.
     * @param ParsDatabaseAdapter $adapter
     */
    public function __construct(ParsDatabaseAdapter $adapter)
    {
    }


    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $handler->handle($request);
    }
}
