<?php


namespace Pars\Core\Emitter;


use Laminas\ConfigAggregator\ConfigAggregator;
use Psr\Container\ContainerInterface;

class EmitterFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $stack = new \Laminas\HttpHandlerRunner\Emitter\EmitterStack();
        $config = $container->get('config');
        if (isset($config[ConfigAggregator::ENABLE_CACHE]) && $config[ConfigAggregator::ENABLE_CACHE] == true) {
            $stack->push(new \Laminas\HttpHandlerRunner\Emitter\SapiStreamEmitter());
        } else {
            $stack->push(new \Laminas\HttpHandlerRunner\Emitter\SapiEmitter());
        }
        return $stack;
    }

}
