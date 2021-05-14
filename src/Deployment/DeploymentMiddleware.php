<?php

namespace Pars\Core\Deployment;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use Laminas\Diactoros\Response\RedirectResponse;
use Pars\Core\Config\ParsConfig;
use Pars\Core\Container\ParsContainer;
use Pars\Core\Container\ParsContainerAwareTrait;
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
    use ParsContainerAwareTrait;
    /**
     * DeploymentMiddleware constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->config = $container->get(ParsConfig::class);
        $this->cacheClearer = $container->get(CacheClearer::class);
        $this->setParsContainer($container->get(ParsContainer::class));
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $key = 'pars';

        if (isset($request->getQueryParams()['clearcache'])) {
            try {
                $key = $this->config->getSecret();
            } catch (Throwable $exception) {
            }
            if ($request->getQueryParams()['clearcache'] == $key) {
                if (!isset($request->getQueryParams()['nopropagate'])) {
                    $this->cacheClearer->clearRemote();
                    $this->config->generateSecret();
                } else {
                    $this->cacheClearer->clear();
                }
                $query = str_replace('&clearcache=' . $key, '', $request->getUri()->getQuery());
                $query = str_replace('?clearcache=' . $key, '', $query);
                $query = str_replace('clearcache=' . $key, '', $query);
                return new RedirectResponse($request->getUri()->withQuery($query));
            }

        }
        return $handler->handle($request);
    }
}
