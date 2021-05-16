<?php


namespace Pars\Core\Application;


use Laminas\HttpHandlerRunner\RequestHandlerRunner;
use Laminas\Stratigility\MiddlewarePipeInterface;
use Mezzio\Application;
use Mezzio\MiddlewareFactory;
use Mezzio\Router\RouteCollector;

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
        $this->initPipeline($app, $factory, $container);
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
     * @return mixed
     */
    abstract protected function initPipeline(
        AbstractApplication $app,
        MiddlewareFactory $factory,
        AbstractApplicationContainer $container
    );

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
