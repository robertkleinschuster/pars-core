<?php

namespace Pars\Core\Deployment;

use Laminas\Diactoros\Response\RedirectResponse;
use Pars\Core\Config\ParsConfig;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class DeploymentMiddleware implements MiddlewareInterface
{
    protected ParsConfig $config;
    protected CacheClearer $cacheClearer;
    /**
     * DeploymentMiddleware constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->config = $container->get(ParsConfig::class);
        $this->cacheClearer = $container->get(CacheClearer::class);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $key = 'pars';
        try {
            $key = $this->config->getSecret();
        } catch (Throwable $exception) {
        }
        if (isset($request->getQueryParams()['clearcache']) && $request->getQueryParams()['clearcache'] == $key) {
            $this->cacheClearer->clear();
            $this->config->getSecret(true);
            $query = str_replace('&clearcache=' . $key, '', $request->getUri()->getQuery());
            $query = str_replace('?clearcache=' . $key, '', $query);
            $query = str_replace('clearcache=' . $key, '', $query);
            return new RedirectResponse($request->getUri()->withQuery($query));
        }
        return $handler->handle($request);
    }
}
