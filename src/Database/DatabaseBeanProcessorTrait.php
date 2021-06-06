<?php


namespace Pars\Core\Database;


use Pars\Bean\Processor\TimestampMetaFieldHandler;
use Pars\Bean\Type\Base\BeanInterface;
use Pars\Bean\Validator\CallbackBeanValidator;
use Pars\Core\Translation\ParsTranslator;
use Pars\Core\Translation\ParsTranslatorAwareTrait;
use Pars\Helper\Validation\ValidationHelperAwareTrait;

trait DatabaseBeanProcessorTrait
{
    use ParsDatabaseAdapterAwareTrait;
    use ParsTranslatorAwareTrait;
    use ValidationHelperAwareTrait;

    public function __construct(ParsDatabaseAdapter $adapter, ParsTranslator $translator = null)
    {
        $this->setDatabaseAdapter($adapter);
        $this->setTranslator($translator);
        $saver = new DatabaseBeanSaver($adapter);
        parent::__construct($saver);
        $this->initSaver($saver);
        $this->initMetaFieldHandler();
        $this->initValidator();
    }

    /**
     * @param DatabaseBeanSaver $saver
     * @return mixed
     */
    abstract protected function initSaver(DatabaseBeanSaver $saver);

    /**
     *
     */
    protected function initMetaFieldHandler()
    {
        $this->addMetaFieldHandler(new TimestampMetaFieldHandler('Timestamp_Edit', 'Timestamp_Create'));
    }

    abstract protected function initValidator();

    /**
     * @param string $function
     * @return $this
     */
    protected function addSaveValidatorFunction(string $function): self
    {
        $this->addSaveValidator(
            new CallbackBeanValidator(
                $function,
                function (BeanInterface $bean) use ($function) {
                    return $this->{$function}($bean);
                }
            )
        );
        return $this;
    }

    /**
     * @param BeanInterface $bean
     * @return bool
     * @throws \Pars\Bean\Type\Base\BeanException
     * @throws \Pars\Pattern\Exception\CoreException
     */
    protected function isBeanAllowedToSave(BeanInterface $bean): bool
    {
        $result = parent::isBeanAllowedToSave($bean);
        if (!$result) {
            $this->getValidationHelper()->addGeneralError($this->translateValidation('general.save'));
        }
        return $result;
    }

    /**
     * @param BeanInterface $bean
     * @return bool
     * @throws \Pars\Bean\Type\Base\BeanException
     * @throws \Pars\Pattern\Exception\CoreException
     */
    protected function isBeanAllowedToDelete(BeanInterface $bean): bool
    {
        $result = parent::isBeanAllowedToDelete($bean);
        if (!$result) {
            $this->getValidationHelper()->addGeneralError($this->translateValidation('general.delete'));
        }
        return $result;
    }
}
