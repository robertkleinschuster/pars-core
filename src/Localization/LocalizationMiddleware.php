<?php

namespace Pars\Core\Localization;

use Laminas\Diactoros\Response\RedirectResponse;
use Locale;
use Mezzio\Authentication\UserInterface;
use Mezzio\Helper\UrlHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

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
    private LocaleFinderInterface $localization;


    /**
     * LocalizationMiddleware constructor.
     * @param UrlHelper $urlHelper
     */
    public function __construct(UrlHelper $urlHelper, array $config, LocaleFinderInterface $localization)
    {
        $this->urlHelper = $urlHelper;
        $this->config = $config;
        $this->localization = $localization;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $routeLocaleCode = Locale::acceptFromHttp($request->getAttribute('locale', null));
        $routeLanguageCode = Locale::getPrimaryLanguage($routeLocaleCode);
        if ($routeLocaleCode) {
            $locale = $this->localization->findLocale($routeLocaleCode, $routeLanguageCode, $this->config['fallback']);
            if ($routeLocaleCode != $locale->getLocale_Code()) {
                if ($this->config['redirect'] === true) {
                    if ($this->urlHelper->getRouteResult()->isSuccess()) {
                        return new RedirectResponse(
                            rtrim($this->urlHelper->generate('cms', ['locale' => $locale->getUrl_Code()]), "/")
                        );
                    }
                }
            }
        } else {
            $headerLocaleCode = Locale::acceptFromHttp($request->getServerParams()['HTTP_ACCEPT_LANGUAGE']);
            $headerLanguageCode = Locale::getPrimaryLanguage($headerLocaleCode);
            $user = $request->getAttribute(UserInterface::class);
            $params = $request->getQueryParams();
            if (isset($params['editlocale'])) {
                $locale = $this->localization->findLocale(
                    $params['editlocale'],
                    Locale::getPrimaryLanguage($params['editlocale']),
                    $this->config['fallback']
                );
            } elseif ($user instanceof LocaleAwareInterface && $user->hasLocale()) {
                $locale = $user->getLocale();
            } else {
                $locale = $this->localization->findLocale(
                    $headerLocaleCode,
                    $headerLanguageCode,
                    $this->config['fallback']
                );
            }
            if ($this->config['redirect'] === true) {
                $this->urlHelper->setBasePath($locale->getUrl_Code());
                if ($this->urlHelper->getRouteResult()->isSuccess()) {
                    return new RedirectResponse(rtrim($this->urlHelper->generate(), '/'));
                }
            }
        }
        return $handler->handle($request->withAttribute(LocaleInterface::class, $locale));
    }
}
