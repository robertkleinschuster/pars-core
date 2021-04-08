<?php

namespace Pars\Core\Task\Order;

use Pars\Bean\Finder\BeanFinderInterface;
use Pars\Bean\Processor\BeanProcessorInterface;
use Pars\Bean\Type\Base\BeanListAwareInterface;
use Pars\Core\Task\Base\AbstractTask;
use Pars\Model\Cms\PageBlock\CmsPageBlockBeanFinder;
use Pars\Model\Cms\PageBlock\CmsPageBlockBeanProcessor;
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
      /*  $this->reinitializeOrderField(
            new LocaleBeanFinder($this->getDbAdapter()),
            new LocaleBeanProcessor($this->getDbAdapter()),
            'Locale_Order'
        );
        $this->reinitializeOrderField(
            new CmsPageBlockBeanFinder($this->getDbAdapter()),
            new CmsPageBlockBeanProcessor($this->getDbAdapter()),
            'CmsPage_CmsBlock_Order'
        );*/
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
        $list = $finder->getBeanFactory()->getEmptyBeanList();
        $beanProcessor = new $beanProcessor($this->adapter);
        foreach ($beanList as $bean) {
            $bean->set($field, 1);
            $list->push($bean);
        }
        $beanProcessor->setBeanList($list);
        $beanProcessor->save();
        foreach ($beanList as $bean) {
            $list = $finder->getBeanFactory()->getEmptyBeanList();
            $beanProcessor = new $beanProcessor($this->adapter);
            $bean->unset($field);
            $list->push($bean);
            $beanProcessor->setBeanList($list);
            $beanProcessor->save();
        }
    }
}
