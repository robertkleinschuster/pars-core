<?php

namespace Pars\Core\Localization;

interface LocaleFinderInterface
{
    public function findLocale(
        ?string $localeCode,
        ?string $language  = null,
        ?string $default = null,
        ?string $domain = null,
        ?string $configDefault = null
    ): LocaleInterface;

    /**
     * @return array
     */
    public function findActiveLocaleCodeList(): array;
}
