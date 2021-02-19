<?php

namespace Pars\Core;

use Laminas\Stratigility\Middleware\ErrorHandler;
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\Session\PhpSession;
use Mezzio\Session\Cache\CacheSessionPersistence;
use Mezzio\Session\SessionPersistenceInterface;
use Pars\Core\Assets\AssetsMiddleware;
use Pars\Core\Assets\AssetsMiddlewareFactory;
use Pars\Core\Authentication\AuthenticationMiddleware;
use Pars\Core\Authentication\AuthenticationMiddlewareFactory;
use Pars\Core\Bundles\BundlesMiddleware;
use Pars\Core\Bundles\BundlesMiddlewareFactory;
use Pars\Core\Database\DatabaseMiddleware;
use Pars\Core\Database\DatabaseMiddlewareFactory;
use Pars\Core\Deployment\DeploymentMiddleware;
use Pars\Core\Deployment\DeploymentMiddlewareFactory;
use Pars\Core\Image\ImageMiddleware;
use Pars\Core\Image\ImageMiddlewareFactory;
use Pars\Core\Localization\LocalizationMiddleware;
use Pars\Core\Localization\LocalizationMiddlewareFactory;
use Pars\Core\Logging\LoggingErrorListenerDelegatorFactory;
use Pars\Core\Logging\LoggingMiddleware;
use Pars\Core\Logging\LoggingMiddlewareFactory;
use Pars\Core\Session\Cache\ParsMultiCachePoolFactory;
use Pars\Core\Translation\TranslatorMiddleware;
use Pars\Core\Translation\TranslatorMiddlewareFactory;

class ConfigProvider
{
    public function __invoke()
    {
        return [
            'dependencies' => $this->getDependencies(),
            'assets' => [
                'development' => false,
                'list' => []
            ],
            'bundles' => [
                'hash' => md5(random_bytes(5)),
                'development' => false,
                'list' => []
            ],
            'image' => [
                'source' => '/u',
                'cache' => '/c',
            ]
        ];
    }

    public function getDependencies(): array
    {
        return [
            'aliases' => [
                SessionPersistenceInterface::class => CacheSessionPersistence::class,
                AuthenticationInterface::class => PhpSession::class,
            ],
            'factories' => [
                'SessionCache' => ParsMultiCachePoolFactory::class,
                AuthenticationMiddleware::class => AuthenticationMiddlewareFactory::class,
                DatabaseMiddleware::class => DatabaseMiddlewareFactory::class,
                TranslatorMiddleware::class => TranslatorMiddlewareFactory::class,
                LoggingMiddleware::class => LoggingMiddlewareFactory::class,
                LocalizationMiddleware::class => LocalizationMiddlewareFactory::class,
                BundlesMiddleware::class => BundlesMiddlewareFactory::class,
                DeploymentMiddleware::class => DeploymentMiddlewareFactory::class,
                AssetsMiddleware::class => AssetsMiddlewareFactory::class,
                ImageMiddleware::class => ImageMiddlewareFactory::class
            ],
            'delegators' => [
                ErrorHandler::class => [
                    LoggingErrorListenerDelegatorFactory::class,
                ],
            ],
        ];
    }
}
