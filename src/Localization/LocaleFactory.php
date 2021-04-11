<?php


namespace Pars\Core\Localization;

use Pars\Pattern\Exception\CoreException;
use Psr\Container\ContainerInterface;

class LocaleFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        if (isset($config['localization']['fallback'])) {
            /**
             * @var $finder LocaleFinderInterface
             */
            $finder = $container->get(LocaleFinderInterface::class);
            $fallback = $config['localization']['fallback'];
            return $finder->findLocale($fallback, null, $fallback);
        }
        throw new CoreException('Fallback locale not configured.');
    }

}
