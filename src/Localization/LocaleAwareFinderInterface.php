<?php


namespace Pars\Core\Localization;


interface LocaleAwareFinderInterface
{
    public function filterLocale_Code(string $code);
}
