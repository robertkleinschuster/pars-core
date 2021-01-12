<?php


namespace Pars\Core\Deployment;


use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\I18n\Translator\TranslatorInterface;
use Pars\Core\Database\DatabaseMiddleware;
use Pars\Core\Translation\TranslatorMiddleware;
use Pars\Model\Localization\Locale\LocaleBeanFinder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DeploymentMiddleware implements MiddlewareInterface
{
    protected array $config;

    /**
     * DeploymentMiddleware constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (isset($request->getQueryParams()['clearcache']) && $request->getQueryParams()['clearcache'] == 'pars') {
            if (file_exists($this->config['config_cache_path'])) {
                unlink($this->config['config_cache_path']);
            }
            $redirect = false;
            if (isset($this->config['bundles']['list'])) {
                foreach ($this->config['bundles']['list'] as $item) {
                    if (isset($item['output'])) {
                        if (file_exists($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $item['output'])) {
                            unlink($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $item['output']);
                        }
                    }
                }
            }

            if (isset($this->config['assets']['list'])) {
                foreach ($this->config['assets']['list'] as $item) {
                    if (isset($item['output'])) {
                        if (file_exists($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $item['output'])) {
                            unlink($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $item['output']);
                        }
                    }
                }
            }

            if (isset($this->config['image']['cache'])) {
                if (is_dir($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $this->config['image']['cache'])) {
                    $this->delTree($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR .$this->config['image']['cache']);
                }
            }

            $translator = $request->getAttribute(TranslatorMiddleware::TRANSLATOR_ATTRIBUTE);
            $adapter = $request->getAttribute(DatabaseMiddleware::ADAPTER_ATTRIBUTE);
            $localeList = null;
            if ($adapter instanceof AdapterInterface) {
                $localeFinder = new LocaleBeanFinder($adapter);
                $localeFinder->setLocale_Active(true);
                $localeList = $localeFinder->getBeanList();
            }
            if ($translator instanceof TranslatorInterface) {
                $redirect = true;
                if (isset($this->config['translator']['translation_file_patterns'])
                    && is_array($this->config['translator']['translation_file_patterns'])) {
                    foreach ($this->config['translator']['translation_file_patterns'] as $translation_file_pattern) {
                        if (isset($translation_file_pattern['text_domain'])) {
                            if ($localeList !== null) {
                                foreach ($localeList as $locale) {
                                    $translator->clearCache(
                                        $translation_file_pattern['text_domain'],
                                        $locale->get('Locale_Code')
                                    );
                                }
                            } elseif (isset($this->config['translator']['locale'])
                                && is_array($this->config['translator']['locale'])
                            ) {
                                foreach ($this->config['translator']['locale'] as $locale) {
                                    $translator->clearCache($translation_file_pattern['text_domain'], $locale);
                                }
                            }
                        }
                    }
                }

                if (isset($this->config['translator']['translation_files'])
                    && is_array($this->config['translator']['translation_files'])
                ) {
                    foreach ($this->config['translator']['translation_files'] as $translation_file) {
                        if (isset($translation_file['text_domain'])) {
                            if ($localeList !== null) {
                                foreach ($localeList as $locale) {
                                    $translator->clearCache(
                                        $translation_file['text_domain'],
                                        $locale->get('Locale_Code')
                                    );
                                }
                            } elseif (isset($this->config['translator']['locale'])
                                && is_array($this->config['translator']['locale'])
                            ) {
                                foreach ($this->config['translator']['locale'] as $locale) {
                                    $translator->clearCache($translation_file['text_domain'], $locale);
                                }
                            }
                        }
                    }
                }

                if (isset($this->config['translator']['remote_translation'])
                    && is_array($this->config['translator']['remote_translation'])
                ) {
                    foreach ($this->config['translator']['remote_translation'] as $item) {
                        if (isset($item['text_domain'])) {
                            if ($localeList !== null) {
                                foreach ($localeList as $locale) {
                                    $translator->clearCache(
                                        $item['text_domain'],
                                        $locale->get('Locale_Code')
                                    );
                                }
                            } elseif (isset($this->config['translator']['locale'])
                                && is_array($this->config['translator']['locale'])
                            ) {
                                foreach ($this->config['translator']['locale'] as $locale) {
                                    $translator->clearCache($item['text_domain'], $locale);
                                }
                            }
                        }
                    }
                }

            }
            if ($redirect) {
                $query = str_replace('&clearcache=pars', '', $request->getUri()->getQuery());
                $query = str_replace('?clearcache=pars', '', $query);
                $query = str_replace('clearcache=pars', '', $query);
                return new RedirectResponse($request->getUri()->withQuery($query));
            }
        }
        return $handler->handle($request);
    }

    public function delTree($dir) {
        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->delTree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }
}
