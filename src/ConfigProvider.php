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
use Pars\Core\Config\ParsApplicationConfig;
use Pars\Core\Config\ParsApplicationConfigFactory;
use Pars\Core\Config\ParsConfig;
use Pars\Core\Config\ParsConfigFactory;
use Pars\Core\Config\ParsConfigMiddleware;
use Pars\Core\Config\ParsConfigMiddlewareFactory;
use Pars\Core\Database\DatabaseMiddleware;
use Pars\Core\Database\DatabaseMiddlewareFactory;
use Pars\Core\Database\ParsDatabaseAdapter;
use Pars\Core\Database\ParsDatabaseAdapterFactory;
use Pars\Core\Deployment\CacheClearer;
use Pars\Core\Deployment\CacheClearerFactory;
use Pars\Core\Deployment\DeploymentMiddleware;
use Pars\Core\Deployment\DeploymentMiddlewareFactory;
use Pars\Core\Deployment\ParsUpdater;
use Pars\Core\Deployment\ParsUpdaterFactory;
use Pars\Core\Deployment\UpdaterInterface;
use Pars\Core\Image\ImageMiddleware;
use Pars\Core\Image\ImageMiddlewareFactory;
use Pars\Core\Localization\LocaleFactory;
use Pars\Core\Localization\LocaleInterface;
use Pars\Core\Localization\LocalizationMiddleware;
use Pars\Core\Localization\LocalizationMiddlewareFactory;
use Pars\Core\Logging\ErrorResolverListenerDelegatorFactory;
use Pars\Core\Logging\LoggingErrorListenerDelegatorFactory;
use Pars\Core\Logging\LoggingMiddleware;
use Pars\Core\Logging\LoggingMiddlewareFactory;
use Pars\Core\Session\Cache\ParsMultiCachePoolFactory;
use Pars\Core\Translation\ParsTranslator;
use Pars\Core\Translation\ParsTranslatorFactory;
use Pars\Core\Translation\TranslatorMiddleware;
use Pars\Core\Translation\TranslatorMiddlewareFactory;

class ConfigProvider
{
    public function __invoke()
    {
        return [
            'config' => [
                'type' => 'base'
            ],
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
            ],
            'localization' => [
                'redirect' => false,
                'domain' => false,
                'fallback' => 'de_AT'
            ],
            'db' => [],
            'translator' => [
                'namespace' => ParsTranslator::NAMESPACE_DEFAULT,
                'locale' => ['de_AT', 'en_US'],
                'translation_file_patterns' => [],
                'translation_files' => [],
                'remote_translation' => [
                    [
                        'type' => \Laminas\I18n\Translator\Loader\RemoteLoaderInterface::class,
                        'text_domain' => 'default'
                    ],
                    [
                        'type' => \Laminas\I18n\Translator\Loader\RemoteLoaderInterface::class,
                        'text_domain' => 'admin'
                    ],
                    [
                        'type' => \Laminas\I18n\Translator\Loader\RemoteLoaderInterface::class,
                        'text_domain' => 'frontend'
                    ]
                ],
                'event_manager_enabled' => true
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
                ImageMiddleware::class => ImageMiddlewareFactory::class,
                LocaleInterface::class => LocaleFactory::class,
                ParsTranslator::class => ParsTranslatorFactory::class,
                ParsConfig::class => ParsConfigFactory::class,
                ParsDatabaseAdapter::class => ParsDatabaseAdapterFactory::class,
                ParsApplicationConfig::class => ParsApplicationConfigFactory::class,
                ParsConfigMiddleware::class => ParsConfigMiddlewareFactory::class,
                CacheClearer::class => CacheClearerFactory::class,
                UpdaterInterface::class => ParsUpdaterFactory::class,
            ],
            'delegators' => [
                ErrorHandler::class => [
                    LoggingErrorListenerDelegatorFactory::class,
                    ErrorResolverListenerDelegatorFactory::class
                ],
            ],
        ];
    }
}
