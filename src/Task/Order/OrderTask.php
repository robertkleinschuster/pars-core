<?php


namespace Pars\Core\Task\Order;


use Niceshops\Bean\Finder\BeanFinderInterface;
use Niceshops\Bean\Processor\BeanProcessorInterface;
use Niceshops\Bean\Type\Base\BeanListAwareInterface;
use Pars\Core\Task\Base\AbstractTask;
use Pars\Model\Localization\Locale\LocaleBeanFinder;
use Pars\Model\Localization\Locale\LocaleBeanProcessor;

/**
 * Class OrderTask
 * @package Pars\Core\Task\Order
 */
class OrderTask extends AbstractTask
{


    /**
     *
     */
    public function execute(): void
    {
        $this->reinitializeOrderField(
            new LocaleBeanFinder($this->getDbAdapter()),
            new LocaleBeanProcessor($this->getDbAdapter()),
            'Locale_Order'
        );
    }


    /**
     * @param BeanFinderInterface $finder
     * @param BeanProcessorInterface $beanProcessor
     * @param string $field
     */
    protected function reinitializeOrderField(
        BeanFinderInterface $finder,
        BeanProcessorInterface $beanProcessor,
        string $field
    ): void {
        $beanList = $finder->getBeanList();
        foreach ($beanList as $bean) {
            $beanProcessor = new $beanProcessor($this->adapter);
            $bean->unset($field);
            if ($beanProcessor instanceof BeanListAwareInterface) {
                $list = $finder->getBeanFactory()->getEmptyBeanList();
                $list->push($bean);
                $beanProcessor->setBeanList($list);
            }
            $beanProcessor->save();
        }
    }
}
