<?php

namespace Pars\Core\Assets;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AssetsMiddleware implements MiddlewareInterface
{
    /**
     *
     * [
     *  1 => [
     *       'root' => 'assets'
     *       'output' => 'assets/banner.png'
     *       'source' => 'mymodule/assets/banner.png
     * ];
     * ]
     * @var array
     */
    protected array $config;

    /**
     * AssetsMiddleware constructor.
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

        foreach ($this->config['list'] as $asset) {
            if (isset($asset['output'])) {
                if ($this->config['development']) {
                    if ($documentRoot->has($asset['output'])) {
                        $documentRoot->delete($asset['output']);
                    }
                }
                $sources = new Filesystem(new Local($asset['root']));
                if (!$documentRoot->has($asset['output'])) {
                    $documentRoot->writeStream(
                        $asset['output'],
                        $sources->readStream($asset['source'])
                    );
                }
            }
        }
        return $handler->handle($request->withAttribute(AssetsMiddleware::class, $this->config));
    }
}
