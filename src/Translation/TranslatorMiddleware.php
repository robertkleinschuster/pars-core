<?php

namespace Pars\Core\Translation;

use Laminas\Db\Adapter\Adapter;
use Laminas\I18n\Translator\Loader\RemoteLoaderInterface;
use Laminas\I18n\Translator\Translator;
use Pars\Core\Database\DatabaseMiddleware;
use Pars\Core\Localization\LocaleInterface;
use Pars\Core\Logging\LoggingMiddleware;
use Pars\Model\Config\ConfigBeanFinder;
use Pars\Model\Translation\TranslationLoader\TranslationBeanFinder;
use Pars\Model\Translation\TranslationLoader\TranslationBeanProcessor;
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
        $adapter = $request->getAttribute(DatabaseMiddleware::ADAPTER_ATTRIBUTE);
        $logger = $request->getAttribute(LoggingMiddleware::LOGGER_ATTRIBUTE);
        if ($locale instanceof LocaleInterface) {
            $this->translator->setLocale($locale->getLocale_Code());
        }
        if ($adapter instanceof Adapter) {
            $fallback = (new ConfigBeanFinder($adapter))->setConfig_Code('locale.default')->getBean()->get('Config_Value');
            $this->translator->setFallbackLocale($fallback);
        }
        if ($logger instanceof LoggerInterface) {
            $this->translator->enableEventManager();
            if ($this->translator->isEventManagerEnabled()) {
                $this->translator->getEventManager()->attach(
                    \Laminas\I18n\Translator\Translator::EVENT_MISSING_TRANSLATION,
                    static function (\Laminas\EventManager\EventInterface $event) use ($logger, $adapter) {
                        $logger->warning('Missing translation', $event->getParams());
                        $data = $event->getParams();
                        if (null !== $adapter) {
                            $translationFinder = new TranslationBeanFinder($adapter);
                            $translationFinder->setLocale_Code($data['locale']);
                            $translationFinder->setTranslation_Code($data['message']);
                            $translationFinder->setTranslation_Namespace($data['text_domain']);
                            if ($translationFinder->count() == 0) {
                                $bean = $translationFinder->getBeanFactory()->getEmptyBean([]);
                                $bean->set('Translation_Code', $data['message']);
                                $bean->set('Locale_Code', $data['locale']);
                                $bean->set('Translation_Namespace', $data['text_domain']);
                                $bean->set('Translation_Text', $data['message']);
                                $beanList = $translationFinder->getBeanFactory()->getEmptyBeanList();
                                $beanList->push($bean);
                                $translationProcessor = new TranslationBeanProcessor($adapter);
                                $translationProcessor->setBeanList($beanList);
                                $translationProcessor->save();
                            }
                        }
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
        $this->translator->getPluginManager()->setFactory(RemoteLoaderInterface::class, function ($container) {
            return $container->get(RemoteLoaderInterface::class);
        });
        return $handler->handle($request->withAttribute(self::TRANSLATOR_ATTRIBUTE, $this->translator));
    }
}
