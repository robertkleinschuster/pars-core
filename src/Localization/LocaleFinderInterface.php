<?php

namespace Pars\Core\Localization;

interface LocaleFinderInterface
{
    public function findLocale(?string $localeCode, ?string $language, $default): LocaleInterface;
}
