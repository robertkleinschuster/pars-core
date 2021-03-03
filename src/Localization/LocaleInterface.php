<?php

namespace Pars\Core\Localization;

interface LocaleInterface
{
    /**
     * @return string
     */
    public function getUrl_Code(): string;

    /**
     * @return string
     */
    public function getLocale_Code(): string;
}
