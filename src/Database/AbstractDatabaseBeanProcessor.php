<?php

namespace Pars\Core\Database;

use Pars\Bean\Processor\AbstractBeanProcessor;
use Pars\Bean\Processor\TimestampMetaFieldHandler;
use Pars\Bean\Type\Base\BeanInterface;
use Pars\Bean\Validator\CallbackBeanValidator;
use Pars\Core\Container\ParsContainer;
use Pars\Core\Container\ParsContainerAwareTrait;
use Pars\Core\Translation\ParsTranslator;
use Pars\Core\Translation\ParsTranslatorAwareInterface;
use Pars\Core\Translation\ParsTranslatorAwareTrait;
use Pars\Helper\Validation\ValidationHelperAwareInterface;
use Pars\Helper\Validation\ValidationHelperAwareTrait;

/**
 * Class AbstractDatabaseBeanProcessor
 * @package Pars\Core\Database
 */
abstract class AbstractDatabaseBeanProcessor extends AbstractBeanProcessor implements
    ParsDatabaseAdapterAwareInterface,
    ValidationHelperAwareInterface,
    ParsTranslatorAwareInterface
{
    use DatabaseBeanProcessorTrait;
}
