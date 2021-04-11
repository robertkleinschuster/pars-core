<?php

namespace Pars\Core\Localization;

interface LocaleFinderInterface
{
    public function findLocale(
        ?string $localeCode,
        ?string $language,
        $default,
        ?string $domain = null,
        ?string $configDefault = null
    ): LocaleInterface;

    /**
     * @return array
     */
    public function getActiveLocaleCodeList(): array;
}
