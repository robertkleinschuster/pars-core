<?php

namespace Pars\Core\Deployment;

use GuzzleHttp\Psr7\Uri;
use Laminas\Diactoros\Response\EmptyResponse;
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
                    ignore_user_abort(true);
                    if ($nopropagate) {
                        $this->cacheClearer->clear();
                    } else {
                        $this->config->generateSecret();
                        $this->cacheClearer->clearRemote();
                        $this->cacheClearer->clear();
                    }
                    return (new RedirectResponse($redirectUri))->withAddedHeader('clear-success', 'true');
                }
            } catch (Throwable $exception) {
                $this->getParsContainer()->getLogger()->error('CLEAR ERROR', ['exception' => $exception]);
            }
        }
        return $handler->handle($request);
    }
}
