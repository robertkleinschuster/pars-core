<?php

namespace Pars\Core\Bundles;

use Laminas\Db\Adapter\AdapterInterface;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use MatthiasMullie\Minify;
use Padaliyajay\PHPAutoprefixer\Autoprefixer;
use Pars\Core\Cache\ParsCache;
use Pars\Core\Config\ParsConfig;
use Pars\Core\Logging\LoggingMiddleware;
use Pars\Helper\Filesystem\FilesystemHelper;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\OutputStyle;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookup;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupCollection;

class BundlesMiddleware implements MiddlewareInterface
{
    /**
     *
     * [
     *  1 => [
     * list => [
     *  'type' => 'js',
     *       'output' => 'bundles/out/bundle.js'
     *        'sources' => [
     *              'bundles/js/jquery.js',
     *              'bundles/js/bootstrap.js',
     *              'bundles/js/my.js',
     *          ]
     * ]
     *
     * ];
     * ]
     * @var array
     */
    protected array $config;

    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * BundlesMiddleware constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->config = $container->get('config')['bundles'];
        $this->container = $container;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $documentRootPath = $request->getServerParams()['DOCUMENT_ROOT'];

        $entryPoints = new EntrypointLookup($documentRootPath . '/build/entrypoints.json');
        $staticFiles = [];
        $staticFiles['js'] = [];
        $staticFiles['css'] = [];

        if (isset($this->config['entrypoints'] ) && is_array($this->config['entrypoints'])) {
            foreach ($this->config['entrypoints'] as $entrypoint) {
                $staticFiles['js'] = array_merge($staticFiles['js'], $entryPoints->getJavaScriptFiles($entrypoint));
                $staticFiles['css'] = array_merge($staticFiles['css'], $entryPoints->getCssFiles($entrypoint));
            }
        }


        if (
            strtolower($request->getHeaderLine('X-Requested-With')) !== 'xmlhttprequest'
            && strtolower($request->getMethod()) === 'get'
        ) {
            $logger = $request->getAttribute(LoggingMiddleware::LOGGER_ATTRIBUTE);
            $hash = $this->config['hash'];
            $documentRootPath = $request->getServerParams()['DOCUMENT_ROOT'];
            $documentRoot = new Filesystem(new Local($documentRootPath));
            $js = [];
            $css = [];
            foreach ($this->config['list'] as $key => $bundle) {
                $bundle['output'] = $this->injectHash($bundle['output'], $hash);
                $this->config['list'][$key]['output'] = $bundle['output'];
                if (isset($bundle['output'])) {
                    if (isset($this->config['development']) && $this->config['development'] === true) {
                        $glob = glob($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $this->globString($bundle['output']));
                        if (is_array($glob)) {
                            foreach ($glob as $file) {
                                $outTime = filemtime($file);
                                $newestTime = 0;
                                if ($bundle['type'] == 'js' && count($bundle['sources'])) {
                                    $newestTime = $this->newestTime($bundle['sources']);
                                }
                                if ($bundle['type'] == 'css' && count($bundle['sources'])) {
                                    $newestTime = $this->newestTime($bundle['sources']);
                                }
                                if ($bundle['type'] == 'scss' && isset($bundle['import'])) {
                                    $newestTime = $this->newestTime($bundle['import']);
                                }

                                if ($newestTime > $outTime) {
                                    if (unlink($file) ) {
                                        if ($logger instanceof LoggerInterface) {
                                            $logger->notice('Deleted ' . $file);
                                        }
                                    } else {
                                        if ($logger instanceof LoggerInterface) {
                                            $logger->error('Error deleting ' . $file);
                                        }
                                    }
                                } else {
                                    $exp = explode(DIRECTORY_SEPARATOR, $file);
                                    $filename = array_pop($exp);
                                    $hash = $this->extractHash($filename);
                                    $this->config['hash'] = $hash;
                                    $this->config['list'][$key]['output'] = $filename;
                                    $bundle['output'] = $filename;
                                }
                            }
                        }
                    }
                    if (!$documentRoot->has($bundle['output'])) {
                        if ($bundle['type'] == 'js' || $bundle['type'] == 'js-sw' && count($bundle['sources'])) {
                            $sources = array_diff($bundle['sources'], $js);
                            $minify = new Minify\JS($sources);
                            $minify->minify($documentRootPath . DIRECTORY_SEPARATOR . $bundle['output']);
                            $js = array_merge($js, $sources);
                            if ($logger instanceof LoggerInterface) {
                                $logger->notice('Wrote ' . $bundle['output']);
                            }
                        }
                        if ($bundle['type'] == 'css' && count($bundle['sources'])) {
                            $sources = array_diff($bundle['sources'], $css);
                            $minify = new Minify\CSS($sources);
                            $minify->minify($documentRootPath . DIRECTORY_SEPARATOR . $bundle['output']);
                            if ($logger instanceof LoggerInterface) {
                                $logger->notice('Wrote ' . $bundle['output']);
                            }
                        }
                        if ($bundle['type'] == 'scss' && isset($bundle['entrypoint']) && isset($bundle['import'])) {
                            $vars = [];
                            if (isset($bundle['config']) && $this->container->has(AdapterInterface::class)) {
                                $config = $this->container->get(ParsConfig::class);
                                $config->setType($bundle['config']);
                                $vars = $config->toArray();
                            }
                            $scss = new Compiler();
                            $scss->setVariables($vars);
                            $scss->setImportPaths($bundle['import']);
                            $scss->setOutputStyle(OutputStyle::COMPRESSED);
                            $scss->setSourceMap(Compiler::SOURCE_MAP_INLINE);
                            $css = $scss->compile('@import "' . $bundle['entrypoint'] . '";');
                            $autoprefixer = new Autoprefixer($css);
                            $css = $autoprefixer->compile(false);
                            if ($documentRoot->write($bundle['output'], $css) ) {
                                if ($logger instanceof LoggerInterface) {
                                    $logger->notice('Wrote ' . $bundle['output']);
                                }
                            } else {
                                if ($logger instanceof LoggerInterface) {
                                    $logger->error('Error writing ' . $bundle['output']);
                                }
                            }
                        }
                    }
                }
            }
        }
        return $handler->handle($request
            ->withAttribute(BundlesMiddleware::class, $this->config)
            ->withAttribute('static_files', $staticFiles)
        );
    }

    protected function injectHash(string $filename, string $hash): string
    {
        return FilesystemHelper::injectHash($filename, $hash);
    }

    protected function extractHash(string $filename)
    {
        return FilesystemHelper::extractHash($filename);
    }

    /**
     * @param string $filename
     * @return string|string[]
     */
    protected function globString(string $filename)
    {
        $hash = $this->extractHash($filename);
        return str_replace($hash, '*', $filename);
    }

    /**
     * @param string $directory
     */
    protected function newestTime($directory)
    {
        return FilesystemHelper::lastModified($directory);
    }
}
