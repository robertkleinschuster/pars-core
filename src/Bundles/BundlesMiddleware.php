<?php


namespace Pars\Core\Bundles;


use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use MatthiasMullie\Minify;

class BundlesMiddleware implements MiddlewareInterface
{
    /**
     *
     * [
     *  1 => [
     *      'type' => 'js',
     *       'output' => 'bundles/out/bundle.js'
     *        'sources' => [
     *              'bundles/js/jquery.js',
     *              'bundles/js/bootstrap.js',
     *              'bundles/js/my.js',
     *          ]
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


    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $documentRootPath = $request->getServerParams()['DOCUMENT_ROOT'];
        $documentRoot = new Filesystem(new Local($documentRootPath));
        foreach ($this->config as $bundle) {
            if (isset($bundle['output']) && !$documentRoot->has($bundle['output'])) {
                if ($bundle['type'] == 'js') {
                    $minify = new Minify\JS($bundle['sources']);
                    $minify->minify($documentRootPath . DIRECTORY_SEPARATOR . $bundle['output']);
                }
                if ($bundle['type'] == 'css') {
                    $minify = new Minify\CSS($bundle['sources']);
                    $minify->minify($documentRootPath . DIRECTORY_SEPARATOR . $bundle['output']);
                }
            }
        }
        return $handler->handle($request->withAttribute(BundlesMiddleware::class, $this->config));
    }

}