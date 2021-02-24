<?php

namespace Pars\Core\Task\Import;

use Pars\Core\Task\Base\AbstractTask;
use Pars\Import\Tesla\TeslaImporter;
use Pars\Model\Import\ImportBeanFinder;
use Pars\Model\Import\ImportBeanProcessor;

/**
 * Class ImportTask
 * @package Pars\Core\Task\Import
 */
class ImportTask extends AbstractTask
{
    public function execute(): void
    {
        $finder = new ImportBeanFinder($this->getDbAdapter());
        foreach ($finder->getBeanList() as $bean) {
            switch ($bean->get('ImportType_Code')) {
                case 'tesla':
                    $importer = new TeslaImporter($bean);
                    if ($importer->isAllowed($this->getNow())) {
                        $importer->run();
                    }
                    $processor = new ImportBeanProcessor($this->getDbAdapter());
                    $beanList = $finder->getBeanFactory()->getEmptyBeanList()->push($importer->getBean());
                    $processor->setBeanList($beanList)->save();
                    break;
            }
        }
    }
}
