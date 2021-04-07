<?php

namespace Pars\Core\Localization;

interface LocaleFinderInterface
{
    public function findLocale(
        ?string $localeCode,
        ?string $language,
        $default,
        ?string $domain = null
    ): LocaleInterface;
}
