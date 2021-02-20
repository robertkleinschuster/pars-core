<?php


namespace Pars\Core\Deployment;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;

class UpdateHandler
{
    public static function postUpdate(Event $event)
    {
        $composer = $event->getComposer();
    }

    public static function postInstall(Event $event)
    {
        $composer = $event->getComposer();
    }

    public static function postAutoloadDump(Event $event)
    {
        $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
        require $vendorDir . '/autoload.php';
    }

    public static function postPackageInstall(PackageEvent $event)
    {
        $installedPackage = $event->getOperation()->getPackage();

    }

    public static function postPackageUpdate(PackageEvent $event)
    {
        $operation = $event->getOperation();

    }
}
