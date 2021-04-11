<?php

namespace Pars\Core\Image;

use Pars\Core\Cache\ParsCache;
use Pars\Core\Config\ParsConfig;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ImageMiddleware implements MiddlewareInterface
{
    public const SERVER_ATTRIBUTE = 'image_server';

    protected ParsConfig $config;

    /**
     * ImageMiddleware constructor.
     * @param ParsConfig $config
     */
    public function __construct(ParsConfig $config)
    {
        $this->config = $config;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $imageConfig = $this->config->getFromAppConfig('image');
        $source = $imageConfig['source'] ?? '/i';
        $cacheDir = $imageConfig['cache'] ?? '/c';
        $server = \League\Glide\ServerFactory::create([
            'cache_with_file_extensions' => true,
            'source' => $_SERVER['DOCUMENT_ROOT'] . $source,
            'cache' => $_SERVER['DOCUMENT_ROOT'] . $cacheDir,
            'max_image_size' => 2000 * 2000,
            'response' => new \League\Glide\Responses\PsrResponseFactory(new \Laminas\Diactoros\Response(), function ($stream) {
                return new \Laminas\Diactoros\Stream($stream);
            }),
        ]);
        if ($request->getUri()->getPath() == '/img') {
            if (empty($_GET['file'])) {
                $this->placeholder($_GET['w'], $_GET['h'], 'aaaaaa', 'ffffff', 'file parameter missing');
            }
            $path = str_replace($source, '', $_GET['file']);
            try {
                $cache = new ParsCache('image');
                $key = $cache->get('key', '');
                if ($key == '' && file_exists('data/image_signature')) {
                    $key = file_get_contents('data/image_signature');
                    $cache->set('key', $key);
                }
                if (empty($key)) {
                    try {
                        $key = $this->config->get('asset.key');
                        $cache->set('key', $key);
                        file_put_contents('data/image_signature', $key);
                    } catch (\Throwable $exception) {
                        $this->placeholder($_GET['w'], $_GET['h'], 'aaaaaa', 'ffffff', $exception->getMessage());
                    }
                }
                \League\Glide\Signatures\SignatureFactory::create($key)->validateRequest('/img', $_GET);
            } catch (\League\Glide\Signatures\SignatureException $e) {
                if (file_exists('data/image_signature')) {
                    unlink('data/image_signature');
                }
                $cache->clear();
                $this->placeholder($_GET['w'], $_GET['h'], 'aaaaaa', 'ffffff', $e->getMessage());
            }
            try {
                /**
                 * @var $response ResponseInterface
                 */
                $response = $server->getImageResponse($path, $_GET);
                $response = $response->withAddedHeader('pragma', 'public');
                return $response;
            } catch (\Throwable $exception) {
                $this->placeholder($_GET['w'], $_GET['h'], 'aaaaaa', 'ffffff', $e->getMessage());
            }
        }
        return $handler->handle($request->withAttribute(self::SERVER_ATTRIBUTE, $server));
    }

    function placeholder($width, $height, $bg_color, $txt_color, $text = null)
    {
        if (!$text) {
            $text = "$width X $height";
        }
        $image = imagecreate($width, $height);
        $bg_color = imagecolorallocate(
            $image,
            base_convert(substr($bg_color, 0, 2), 16, 10),
            base_convert(substr($bg_color, 2, 2), 16, 10),
            base_convert(substr($bg_color, 4, 2), 16, 10)
        );
        $txt_color = imagecolorallocate(
            $image,
            base_convert(substr($txt_color, 0, 2), 16, 10),
            base_convert(substr($txt_color, 2, 2), 16, 10),
            base_convert(substr($txt_color, 4, 2), 16, 10)
        );
        imagefill($image, 0, 0, $bg_color);
        $fontsize = ($width > $height) ? ($height / 10) : ($width / 10);
        imagettftext($image, $fontsize, 0, 0, ($height / 2) + ($fontsize * 0.2), $txt_color, __DIR__ . DIRECTORY_SEPARATOR . 'HelveticaNeue.ttf', $text);
        header("Content-Type: image/png");
        imagepng($image);
        imagedestroy($image);
        exit;
    }
}
