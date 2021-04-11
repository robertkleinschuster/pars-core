<?php


namespace Pars\Core\Translation;


interface MissingTranslationSaverInterface
{
    public function saveMissingTranslation(string $locale, string $code, string $namespace);

}
