<?php

namespace Pars\Core\Translation;

use Laminas\I18n\Translator\Loader\RemoteLoaderInterface;
use Pars\Core\Config\ParsConfig;
use Pars\Core\Localization\LocaleInterface;
use Pars\Core\Logging\LoggingMiddleware;
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
    public const TRANSLATOR_ATTRIBUTE = 'translator';

    /**
     * @var ParsTranslator
     */
    private ParsTranslator $translator;

    private ParsConfig $config;

    /**
     * TranslationMiddleware constructor.
     * @param ParsTranslator $translator
     */
    public function __construct(ParsTranslator $translator, ParsConfig $config)
    {
        $this->translator = $translator;
        $this->config = $config;
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
        $logger = $request->getAttribute(LoggingMiddleware::LOGGER_ATTRIBUTE);
        if ($locale instanceof LocaleInterface) {
            $this->translator->setLocale($locale);
        }
        $this->translator->getTranslator()->setFallbackLocale($this->config->get('locale.default'));
        $this->translator->getTranslator()->enableEventManager();
        if ($this->translator->getTranslator()->isEventManagerEnabled()) {
            $this->translator->getTranslator()->getEventManager()->attach(
                \Laminas\I18n\Translator\Translator::EVENT_MISSING_TRANSLATION,
                function (\Laminas\EventManager\EventInterface $event) use ($logger) {
                    $data = $event->getParams();
                    if ($logger instanceof LoggerInterface) {
                        $logger->warning('Missing translation', $data);
                    }
                    $this->translator->saveMissingTranslation($data['locale'], $data['message'], $data['text_domain']);
                }
            );
            $this->translator->getTranslator()->getEventManager()->attach(
                \Laminas\I18n\Translator\Translator::EVENT_NO_MESSAGES_LOADED,
                function (\Laminas\EventManager\EventInterface $event) use ($logger) {
                    if ($logger instanceof LoggerInterface) {
                        $logger->error('No messages loaded', $event->getParams());
                    }
                }
            );
        }
        $this->translator->getTranslator()
            ->getPluginManager()
            ->setFactory(RemoteLoaderInterface::class, function ($container) {
                return $container->get(RemoteLoaderInterface::class);
            });
        return $handler->handle($request
            ->withAttribute(self::TRANSLATOR_ATTRIBUTE, $this->translator->getTranslator())
            ->withAttribute(ParsTranslator::class, $this->translator)
        );
    }
}
