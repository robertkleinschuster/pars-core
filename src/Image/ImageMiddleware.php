<?php


namespace Pars\Core\Image;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ImageMiddleware implements MiddlewareInterface
{
    public const SERVER_ATTRIBUTE = 'image_server';

    protected array $config;

    /**
     * ImageMiddleware constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $source = $this->config['source'] ?? '/i';
        $cache = $this->config['cache'] ?? '/c';
        $server = \League\Glide\ServerFactory::create([
            'source' => $_SERVER['DOCUMENT_ROOT'] . $source,
            'cache' => $_SERVER['DOCUMENT_ROOT'] . $cache,
            'max_image_size' => 2000 * 2000,
            'response' => new \League\Glide\Responses\PsrResponseFactory(new \Laminas\Diactoros\Response(), function ($stream) {
                return new \Laminas\Diactoros\Stream($stream);
            }),
        ]);
        if ($request->getUri()->getPath() == '/img') {
            if (!isset($_GET['file'])) {
                return new \Laminas\Diactoros\Response\HtmlResponse('file parameter missing');
            }
            $path = str_replace($source, '', $_GET['file']);
            try {
                $key = '';
                if (file_exists('data/image_signature')) {
                    $key = file_get_contents('data/image_signature');
                }
                \League\Glide\Signatures\SignatureFactory::create($key)->validateRequest('/img', $_GET);
            } catch (\League\Glide\Signatures\SignatureException $e) {
                return new \Laminas\Diactoros\Response\HtmlResponse($e->getMessage());
            }
            return $server->getImageResponse($path, $_GET);
        }
        return $handler->handle($request->withAttribute(self::SERVER_ATTRIBUTE, $server));
    }

}
