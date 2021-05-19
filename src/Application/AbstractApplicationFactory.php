<?php


namespace Pars\Core\Application;


use Laminas\HttpHandlerRunner\RequestHandlerRunner;
use Laminas\Stratigility\MiddlewarePipeInterface;
use Mezzio\Application;
use Mezzio\MiddlewareFactory;
use Mezzio\Router\RouteCollector;
use Laminas\Stratigility\Middleware\ErrorHandler;
use Mezzio\Csrf\CsrfMiddleware;
use Mezzio\Flash\FlashMessageMiddleware;
use Mezzio\Handler\NotFoundHandler;
use Mezzio\Helper\ServerUrlMiddleware;
use Mezzio\Helper\UrlHelperMiddleware;
use Mezzio\Router\Middleware\DispatchMiddleware;
use Mezzio\Router\Middleware\ImplicitHeadMiddleware;
use Mezzio\Router\Middleware\ImplicitOptionsMiddleware;
use Mezzio\Router\Middleware\MethodNotAllowedMiddleware;
use Mezzio\Router\Middleware\RouteMiddleware;
use Pars\Core\Config\ParsConfig;
use Pars\Core\Deployment\DeploymentMiddleware;
use Pars\Core\Deployment\UpdateMiddleware;
use Pars\Core\Image\ImageMiddleware;
use Pars\Core\Localization\LocalizationMiddleware;
use Pars\Core\Session\ParsSessionMiddleware;
use Pars\Core\Translation\TranslatorMiddleware;

/**
 * Class AbstractApplicationFactory
 * @package Pars\Core\Application
 */
abstract class AbstractApplicationFactory
{
    /**
     * @param AbstractApplicationContainer $container
     * @return Application
     */
    public function __invoke(AbstractApplicationContainer $container): Application
    {
        $factory = $container->get(MiddlewareFactory::class);
        $pipeline = $container->get('Mezzio\ApplicationPipeline');
        $routes = $container->get(RouteCollector::class);
        $runner = $container->get(RequestHandlerRunner::class);
        $app = $this->createApplication($factory, $pipeline, $routes, $runner);
        $this->initBasePipeline($app, $factory, $container);
        $this->initRoutes($app, $factory, $container);
        return $app;
    }

    /**
     * @param MiddlewareFactory $factory
     * @param MiddlewarePipeInterface $pipeline
     * @param RouteCollector $routes
     * @param RequestHandlerRunner $runner
     * @return AbstractApplication
     */
    abstract protected function createApplication(
        MiddlewareFactory $factory,
        MiddlewarePipeInterface $pipeline,
        RouteCollector $routes,
        RequestHandlerRunner $runner
    ): AbstractApplication;

    /**
     * @param AbstractApplication $app
     * @param MiddlewareFactory $factory
     * @param AbstractApplicationContainer $container
     */
    private function initBasePipeline(
        AbstractApplication $app,
        MiddlewareFactory $factory,
        AbstractApplicationContainer $container
    ) {
        $app->pipe(ErrorHandler::class);
        $app->pipe(ServerUrlMiddleware::class);
        $app->pipe(DeploymentMiddleware::class);
        $app->pipe(UpdateMiddleware::class);
        $config = $container->get(ParsConfig::class);
        $app->pipe($config->get('image.path'), ImageMiddleware::class);
        $app->pipe(ParsSessionMiddleware::class);
        $app->pipe(FlashMessageMiddleware::class);
        $app->pipe(CsrfMiddleware::class);
        $app->pipe(RouteMiddleware::class);
        $app->pipe(ImplicitHeadMiddleware::class);
        $app->pipe(ImplicitOptionsMiddleware::class);
        $app->pipe(MethodNotAllowedMiddleware::class);
        $app->pipe(UrlHelperMiddleware::class);
        $app->pipe(LocalizationMiddleware::class);
        $app->pipe(TranslatorMiddleware::class);
        $this->initPipeline($app, $factory, $container);
        $app->pipe(DispatchMiddleware::class);
        $app->pipe(NotFoundHandler::class);
    }

    protected function initPipeline(
        AbstractApplication $app,
        MiddlewareFactory $factory,
        AbstractApplicationContainer $container
    ) {}

    /**
     * @param AbstractApplication $app
     * @param MiddlewareFactory $factory
     * @param AbstractApplicationContainer $container
     * @return mixed
     */
    abstract protected function initRoutes(
        AbstractApplication $app,
        MiddlewareFactory $factory,
        AbstractApplicationContainer $container
    );
}
