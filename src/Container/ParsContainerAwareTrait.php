<?php


namespace Pars\Core\Container;


trait ParsContainerAwareTrait
{
    protected ParsContainer $parsContainer;

    /**
     * @return ParsContainer
     */
    public function getParsContainer(): ParsContainer
    {
        return $this->parsContainer;
    }

    /**
     * @param ParsContainer $parsContainer
     */
    public function setParsContainer(ParsContainer $parsContainer): void
    {
        $this->parsContainer = $parsContainer;
    }


}
