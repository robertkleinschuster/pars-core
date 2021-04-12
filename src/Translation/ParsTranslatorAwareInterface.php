<?php


namespace Pars\Core\Translation;


interface ParsTranslatorAwareInterface
{
    /**
     * @return ParsTranslator
     */
    public function getTranslator(): ParsTranslator;

    /**
     * @param ParsTranslator $translator
     *
     * @return $this
     */
    public function setTranslator(ParsTranslator $translator): self;
    /**
     * @return bool
     */
    public function hasTranslator(): bool;
}
