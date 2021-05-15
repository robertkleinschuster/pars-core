<?php

namespace Pars\Core\Emitter;

use Psr\Container\ContainerInterface;

class EmitterFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        $stack = new \Laminas\HttpHandlerRunner\Emitter\EmitterStack();
        if (isset($config['emitter']) && $config['emitter'] == 'stream') {
            $stack->push(new \Laminas\HttpHandlerRunner\Emitter\SapiStreamEmitter());
        }
        $stack->push(new \Laminas\HttpHandlerRunner\Emitter\SapiEmitter());
        return $stack;
    }
}
