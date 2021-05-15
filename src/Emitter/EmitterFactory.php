<?php

namespace Pars\Core\Emitter;

use Psr\Container\ContainerInterface;

class EmitterFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $stack = new \Laminas\HttpHandlerRunner\Emitter\EmitterStack();
        $stack->push(new \Laminas\HttpHandlerRunner\Emitter\SapiStreamEmitter());
        $stack->push(new \Laminas\HttpHandlerRunner\Emitter\SapiEmitter());
        return $stack;
    }
}
