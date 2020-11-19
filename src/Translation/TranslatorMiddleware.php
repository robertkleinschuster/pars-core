<?php

namespace Pars\Core\Translation;

use Pars\Core\Localization\LocaleInterface;
use Pars\Core\Logging\LoggingMiddleware;
use Laminas\I18n\Translator\Translator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class TranslatorMiddleware
 * @package Pars\Core\Translation
 */
class TranslatorMiddleware implements MiddlewareInterface
{
    public const TRANSLATOR_ATTRIBUTE = 'translater';

    /**
     * @var Translator
     */
    private Translator $translator;

    /**
     * TranslationMiddleware constructor.
     * @param Translator $translator
     * @param array $config
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }


    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $locale = $request->getAttribute(LocaleInterface::class);
        $logger = $request->getAttribute(LoggingMiddleware::LOGGER_ATTRIBUTE);
        if ($locale instanceof LocaleInterface) {
            $this->translator->setLocale($locale->getLocale_Code());
        }
        if ($logger instanceof LoggerInterface) {
            $this->translator->enableEventManager();
            if ($this->translator->isEventManagerEnabled()) {
                $this->translator->getEventManager()->attach(
                    \Laminas\I18n\Translator\Translator::EVENT_MISSING_TRANSLATION,
                    static function (\Laminas\EventManager\EventInterface $event) use ($logger) {
                        $logger->warning('Missing translation', $event->getParams());
                    }
                );
                $this->translator->getEventManager()->attach(
                    \Laminas\I18n\Translator\Translator::EVENT_NO_MESSAGES_LOADED,
                    static function (\Laminas\EventManager\EventInterface $event) use ($logger) {
                        $logger->error('No messages loaded', $event->getParams());
                    }
                );
            }
        }
        return $handler->handle($request->withAttribute(self::TRANSLATOR_ATTRIBUTE, $this->translator));
    }
}
