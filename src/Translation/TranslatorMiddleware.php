<?php

namespace Pars\Core\Translation;

use Pars\Core\Container\ParsContainer;
use Pars\Core\Container\ParsContainerAwareTrait;
use Pars\Core\Localization\LocaleInterface;
use Pars\Pattern\Exception\CoreException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class TranslatorMiddleware
 * @package Pars\Core\Translation
 */
class TranslatorMiddleware implements MiddlewareInterface
{
    use ParsContainerAwareTrait;

    /**
     * @var ParsTranslator
     */
    private ParsTranslator $translator;

    /**
     * TranslatorMiddleware constructor.
     * @param ParsContainer $parsContainer
     * @throws CoreException
     */
    public function __construct(ParsContainer $parsContainer)
    {
        $this->translator = $parsContainer->getTranslator();
        $this->setParsContainer($parsContainer);
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $locale = $request->getAttribute(LocaleInterface::class);
        if ($locale instanceof LocaleInterface) {
            $this->translator->setLocale($locale);
        }
        return $handler->handle($request);
    }
}
