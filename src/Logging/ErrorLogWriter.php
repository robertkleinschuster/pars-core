<?php


namespace Pars\Core\Logging;


use Laminas\Log\Writer\AbstractWriter;

class ErrorLogWriter extends AbstractWriter
{
    protected function doWrite(array $event)
    {
        $message = $this->formatter->format($event);
        error_log('LOG: ' . $message, 4);
    }

}
