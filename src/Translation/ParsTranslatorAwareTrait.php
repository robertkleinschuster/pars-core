<?php


namespace Pars\Core\Translation;


use Pars\Bean\Type\Base\BeanException;
use Pars\Pattern\Exception\CoreException;

trait ParsTranslatorAwareTrait
{
    /**
     * @var ParsTranslator
     */
    protected ?ParsTranslator $translator = null;

    /**
     * @return ParsTranslator
     */
    public function getTranslator(): ParsTranslator
    {
        if (!$this->hasTranslator()) {
            throw new CoreException('Translator not set');
        }
        return $this->translator;
    }

    /**
     * @param ParsTranslator $translator
     *
     * @return $this
     */
    public function setTranslator(?ParsTranslator $translator): self
    {
        $this->translator = $translator;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasTranslator(): bool
    {
        return isset($this->translator);
    }


    /**
     * @param string $code
     * @param array $vars
     * @param string|null $namespace
     * @return string
     * @throws CoreException
     * @throws BeanException
     */
    public function translate(string $code, array $vars = [], ?string $namespace = null): string
    {
        if ($this->hasTranslator()) {

            return $this->getTranslator()->translate($code, $vars, $namespace);
        }
        return $code;
    }

    /**
     * @param string $code
     * @param array $vars
     * @return string
     * @throws BeanException
     * @throws CoreException
     */
    public function translateValidation(string $code, array $vars = []): string
    {
        if ($this->hasTranslator()) {
            return $this->getTranslator()->translate($code, $vars, ParsTranslator::NAMESPACE_VALIDATION);
        }
        return $code;
    }

    /**
     * @param string $code
     * @param int $count
     * @param array $vars
     * @param string|null $namespace
     * @return string
     * @throws CoreException
     * @throws BeanException
     */
    public function translatepl(string $code, int $count, array $vars = [], ?string $namespace = null): string
    {
        if ($this->hasTranslator()) {
            return $this->getTranslator()->translatepl($code, $count, $vars, $namespace);
        }
        return $code;
    }
}
