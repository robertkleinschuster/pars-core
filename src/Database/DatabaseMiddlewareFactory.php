<?php

declare(strict_types=1);

namespace Pars\Core\Database;

use Laminas\Db\Adapter\AdapterInterface;
use Psr\Container\ContainerInterface;

class DatabaseMiddlewareFactory
{

    public function __invoke(ContainerInterface $container): DatabaseMiddleware
    {
        return new DatabaseMiddleware($container->get(ParsDbAdapter::class));
    }
}
