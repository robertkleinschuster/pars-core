<?php


namespace Pars\Core\Config;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ParsConfigMiddleware implements MiddlewareInterface
{
    protected ParsConfig $config;

    /**
     * ParsConfigMiddleware constructor.
     * @param ParsConfig $config
     */
    public function __construct(ParsConfig $config)
    {
        $this->config = $config;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $handler->handle($request->withAttribute(ParsConfig::class, $this->config));
    }

}
