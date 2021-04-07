<?php

namespace Pars\Core\Deployment;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\Installer\PackageEvent;
use Composer\Script\Event;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class UpdateHandler
 * @package Pars\Core\Deployment
 */
class UpdateHandler
{
    /**
     * @var array
     */
    protected static array $changedPackages = [];

    /**
     * @param array $changedPackages
     */
    public static function updateApps(array $changedPackages)
    {
        if (count($changedPackages)) {
            /**
             * @var ContainerInterface $container
             */
            $container = require 'config/container.php';
            $config = $container->get('config');
            if (isset($config['master-app']) && $config['master-app']) {
                $glob = glob('src/*/config/container.php');
                $root = getcwd();
                foreach ($glob as $containerFile) {
                    if (is_dir(dirname($containerFile, 2))) {
                        chdir(dirname($containerFile, 2));
                        self::handleAppUpdate(require 'config/container.php');
                        chdir($root);
                    }
                }
            } else {
                self::handleAppUpdate($container);
            }
        }
    }

    /**
     * @param ContainerInterface $container
     */
    public static function handleAppUpdate(ContainerInterface $container)
    {
        self::log($container, 'Pars Update');
        $adapter = $container->get(\Laminas\Db\Adapter\AdapterInterface::class);
        $translator = $container->get(\Laminas\I18n\Translator\TranslatorInterface::class);
        self::log($container, 'Pars Clear Cache');
        $cache = new \Pars\Core\Deployment\Cache($container->get('config'), $adapter);
        $cache->setTranslator($translator);
        $cache->clear();
        $dataUpdate = new \Pars\Core\Database\Updater\SchemaUpdater($adapter);
        $result = $dataUpdate->executeSilent();
        self::log($container, json_encode($result, JSON_PRETTY_PRINT));
        $dataUpdate = new \Pars\Core\Database\Updater\DataUpdater($adapter);
        $result = $dataUpdate->executeSilent();
        self::log($container, json_encode($result, JSON_PRETTY_PRINT));
        $dataUpdate = new \Pars\Core\Database\Updater\SpecialUpdater($adapter);
        $result = $dataUpdate->executeSilent();
        self::log($container, json_encode($result, JSON_PRETTY_PRINT));
    }

    public static function log(ContainerInterface $container, $msg) {
        $logger = $container->get('Logger');
        if ($logger instanceof LoggerInterface) {
            $logger->info($msg);
        }
    }

    /**
     * @param Event $event
     */
    public static function postUpdate(Event $event)
    {
        self::updateApps(self::$changedPackages);
    }

    /**
     * @param Event $event
     */
    public static function postInstall(Event $event)
    {
        self::updateApps(self::$changedPackages);
    }

    /**
     * @param Event $event
     */
    public static function postAutoloadDump(Event $event)
    {
        $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
        require $vendorDir . '/autoload.php';
    }

    /**
     * @param PackageEvent $event
     */
    public static function postPackageInstall(PackageEvent $event)
    {
        $operation = $event->getOperation();
        if ($operation instanceof InstallOperation) {
            $name = $operation->getPackage()->getUniqueName();
            self::$changedPackages[$name] = $operation->getPackage()->getVersion();
        }
    }

    /**
     * @param PackageEvent $event
     */
    public static function postPackageUpdate(PackageEvent $event)
    {
        $operation = $event->getOperation();
        if ($operation instanceof UpdateOperation) {
            $name = $operation->getTargetPackage()->getUniqueName();
            self::$changedPackages[$name] = $operation->getTargetPackage()->getVersion();
        }
    }
}
