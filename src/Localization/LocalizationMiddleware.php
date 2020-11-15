<?php

namespace Pars\Core\Localization;

use Laminas\Diactoros\Response\RedirectResponse;
use Locale;
use Mezzio\Authentication\UserInterface;
use Mezzio\Helper\UrlHelper;
use Pars\Core\Database\DatabaseMiddleware;
use Pars\Core\Logging\LoggingMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class LocalizationMiddleware
 * @package Pars\Core\Localization
 */
class LocalizationMiddleware implements MiddlewareInterface
{

    /**
     * @var UrlHelper
     */
    private UrlHelper $urlHelper;
    private array $config;

    public const LOCALIZATION_ATTRIBUTE = 'locale';

    /**
     * LocalizationMiddleware constructor.
     * @param UrlHelper $urlHelper
     */
    public function __construct(UrlHelper $urlHelper, array $config)
    {
        $this->urlHelper = $urlHelper;
        $this->config = $config;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $locale = $request->getAttribute('locale', false);
        $locale = Locale::acceptFromHttp($locale);
        $redirect = $this->config['redirect'];
        $basePath = $this->config['default'];
        $user = $request->getAttribute(UserInterface::class);
        if ($user instanceof UserBean && $locale === false) {
            $locale = $user->hasData('Locale_Code') ? $user->getData('Locale_Code') : $locale;
        }
        try {
            if ($locale === false || $locale === null) {
                $adapter = $request->getAttribute(DatabaseMiddleware::ADAPTER_ATTRIBUTE);
                $locale = Locale::acceptFromHttp($request->getServerParams()['HTTP_ACCEPT_LANGUAGE']);
                if ($locale !== false) {
                    $finder = new LocaleBeanFinder($adapter);
                    $finder->setLocale_Code($locale);
                    $finder->setLocale_Active(true);
                    $finder->limit(1, 0);
                    if ($finder->count() == 1) {
                        $basePath = $finder->getBean()->getData('Locale_UrlCode');
                    } else {
                        $locale = false;
                    }
                }
                if ($locale === false) {
                    $finder = new LocaleBeanFinder($adapter);
                    $finder->setLanguage(Locale::getPrimaryLanguage(Locale::acceptFromHttp($request->getServerParams()['HTTP_ACCEPT_LANGUAGE'])));
                    $finder->setLocale_Active(true);
                    $finder->limit(1, 0);
                    if ($finder->count() == 1) {
                        $basePath = $finder->getBean()->getData('Locale_UrlCode');
                    } else {
                        $locale = false;
                    }
                }
                if ($locale === false) {
                    $finder = new LocaleBeanFinder($adapter);
                    $finder->setLocale_Active(true);
                    $finder->limit(1, 0);
                    $basePath = $finder->getBean()->getData('Locale_UrlCode');
                }
            }
        } catch (\Exception $exception) {
            $basePath = $this->config['default'];
            $logger = $request->getAttribute(LoggingMiddleware::LOGGER_ATTRIBUTE);
            if ($logger instanceof LoggerInterface) {
                $logger->error('Error finding locale for request!', ['exception' => $exception]);
            }
        }
        if ($redirect) {
            $this->urlHelper->setBasePath($basePath);
            return new RedirectResponse($this->urlHelper->generate());
        }
        return $handler->handle($request->withAttribute(self::LOCALIZATION_ATTRIBUTE, $locale));
    }
}
