<?php

namespace Pars\Core\Localization;

use Laminas\Diactoros\Response\RedirectResponse;
use Locale;
use Mezzio\Authentication\UserInterface;
use Mezzio\Helper\UrlHelper;
use Pars\Core\Config\ParsConfig;
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
    private LocaleFinderInterface $localization;
    private ParsConfig $config;


    /**
     * LocalizationMiddleware constructor.
     * @param UrlHelper $urlHelper
     */
    public function __construct(
        UrlHelper $urlHelper,
        ParsConfig $parsConfig,
        LocaleFinderInterface $localization
    ) {
        $this->urlHelper = $urlHelper;
        $this->localization = $localization;
        $this->config = $parsConfig;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $localizationConfig = $this->config->getApplicationConfig()->get('localization');
        $useDomain = $localizationConfig['domain'] ?? false;
        $fallback = $localizationConfig['fallback'];
        $redirect = $localizationConfig['redirect'];
        $domain = null;
        if ($useDomain) {
            $domain = $request->getUri()->getHost();
        }
        $routeLocaleCode = Locale::acceptFromHttp($request->getAttribute('locale', null));
        $routeLanguageCode = Locale::getPrimaryLanguage($routeLocaleCode);
        $configDefault = $this->config->get('locale.default');
        $params = $request->getServerParams();

        if ($routeLocaleCode) {
            $locale = $this->localization->findLocale(
                $routeLocaleCode,
                $routeLanguageCode,
                $fallback,
                $domain,
                $configDefault
            );
            if ($routeLocaleCode != $locale->getLocale_Code()) {
                if ($redirect === true) {
                    if ($this->urlHelper->getRouteResult()->isSuccess()) {
                        return new RedirectResponse(
                            rtrim($this->urlHelper->generate(null, ['locale' => $locale->getUrl_Code()]), "/")
                        );
                    }
                }
            }
        } elseif (isset($params['HTTP_ACCEPT_LANGUAGE'])) {
            $headerLocaleCode = Locale::acceptFromHttp($params['HTTP_ACCEPT_LANGUAGE']);
            $headerLanguageCode = Locale::getPrimaryLanguage($headerLocaleCode);
            $user = $request->getAttribute(UserInterface::class);
            $params = $request->getQueryParams();
            if (isset($params['editlocale'])) {
                $locale = $this->localization->findLocale(
                    $params['editlocale'],
                    Locale::getPrimaryLanguage($params['editlocale']),
                    $fallback,
                    null,
                    $configDefault
                );
            } elseif ($user instanceof LocaleAwareInterface && $user->hasLocale()) {
                $locale = $user->getLocale();
            } else {
                $locale = $this->localization->findLocale(
                    $headerLocaleCode,
                    $headerLanguageCode,
                    $fallback,
                    $domain,
                    $configDefault
                );
            }
            if ($redirect === true) {
                $this->urlHelper->setBasePath($locale->getUrl_Code());
                if ($this->urlHelper->getRouteResult()->isSuccess()) {
                    return new RedirectResponse(rtrim($this->urlHelper->generate(), '/'));
                }
            }
        } else {
            $locale = $this->localization->findLocale($this->config->get('locale.default'));
        }
        return $handler->handle($request->withAttribute(LocaleInterface::class, $locale));
    }
}
