<?php

namespace Pars\Core\Localization;

/**
 * Interface LocaleAwareInterface
 * @package Pars\Core\Localization
 */
interface LocaleAwareInterface
{
    /**
     * @return LocaleInterface
     */
    public function getLocale(): LocaleInterface;

    /**
     * @return LocaleInterface
     */
    public function hasLocale(): bool;
}
