<?php

namespace Pars\Core\Deployment;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use Laminas\Diactoros\Response;
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

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $params = $request->getQueryParams();
        $clearcache = $params['clearcache'] ?? false;
        $nopropagate = $params['nopropagate'] ?? false;
        if ($clearcache) {
            try {
                $key = $this->config->getSecret();
                $keyNew = $this->config->getSecret(true);
                $redirectUri = Uri::withoutQueryValue($request->getUri(), 'clearcache');
                if ($clearcache == $key || $clearcache == $keyNew) {
                    if ($nopropagate) {
                        $this->cacheClearer->clear();
                    } else {
                        $this->config->generateSecret();
                        $this->cacheClearer->clear();
                        $this->cacheClearer->clearRemote($request->getUri());
                    }
                    return new RedirectResponse($redirectUri);
                } else {
                    return new RedirectResponse($redirectUri, 403);
                }
            } catch (Throwable $exception) {
                $this->getParsContainer()->getLogger()->error('CLEAR ERROR', ['exception' => $exception]);
            }
        }
        return $handler->handle($request);
    }
}
