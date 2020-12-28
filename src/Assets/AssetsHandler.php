<?php


namespace Pars\Core\Assets;


use Laminas\Diactoros\Response;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AssetsHandler implements RequestHandlerInterface
{

    protected array $config;

    /**
     * AssetsHandler constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $type = $request->getAttribute('type');
        $name = $request->getAttribute('name');
        $filesystem = new Filesystem(new Local($this->config['root']));
        $file = $filesystem->get("$name.$type");
        return (new Response($file->readStream()))->withHeader('Content-Type', $file->getMimetype());
    }

    /**
     * @return string
     */
    public static function getRoute()
    {
        return '/assets/{type}/{name}';
    }

}
