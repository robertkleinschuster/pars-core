<?php


namespace Pars\Core\Bundles;


use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use MatthiasMullie\Minify;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

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
     * BundlesMiddleware constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $documentRootPath = $request->getServerParams()['DOCUMENT_ROOT'];
        $documentRoot = new Filesystem(new Local($documentRootPath));
        foreach ($this->config['list'] as $bundle) {
            if (isset($bundle['output'])) {
                if ($this->config['development']) {
                    if ($documentRoot->has($bundle['output'])) {
                        $documentRoot->delete($bundle['output']);
                    }
                }
                if (!$documentRoot->has($bundle['output'])) {
                    if ($bundle['type'] == 'js' && count($bundle['sources'])) {
                        $minify = new Minify\JS($bundle['sources']);
                        $minify->minify($documentRootPath . DIRECTORY_SEPARATOR . $bundle['output']);
                    }
                    if ($bundle['type'] == 'css' && count($bundle['sources'])) {
                        $minify = new Minify\CSS($bundle['sources']);
                        $minify->minify($documentRootPath . DIRECTORY_SEPARATOR . $bundle['output']);
                    }
                }
            }
        }
        return $handler->handle($request->withAttribute(BundlesMiddleware::class, $this->config));
    }

}
