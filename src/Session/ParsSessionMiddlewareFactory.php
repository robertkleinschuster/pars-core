<?php


namespace Pars\Core\Session;


use Mezzio\Session\SessionMiddleware;
use Mezzio\Session\SessionMiddlewareFactory;
use Psr\Container\ContainerInterface;

class ParsSessionMiddlewareFactory extends SessionMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): SessionMiddleware
    {
        $factory = new ParsSessionPersistenceFactory();
        return new ParsSessionMiddleware($factory($container));
    }
}
