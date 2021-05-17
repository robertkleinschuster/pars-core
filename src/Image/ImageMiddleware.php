<?php

namespace Pars\Core\Image;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\Stream;
use League\Glide\Responses\PsrResponseFactory;
use League\Glide\ServerFactory;
use League\Glide\Signatures\SignatureException;
use League\Glide\Signatures\SignatureFactory;
use Pars\Core\Cache\ParsCache;
use Pars\Core\Config\ParsConfig;
use Pars\Helper\Filesystem\FilesystemHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ImageMiddleware implements MiddlewareInterface
{
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
        $source = $this->config->get('image.source');
        $source = FilesystemHelper::getPath("public/$source");
        $cacheDir = $this->config->get('image.cache');
        $cacheDir = FilesystemHelper::getPath("public/$cacheDir");
        $basePath = $this->config->get('image.path');
        $server = ServerFactory::create([
            'cache_with_file_extensions' => true,
            'source' => $source,
            'cache' =>  $cacheDir,
            'max_image_size' => 2000 * 2000,
            'response' => new PsrResponseFactory(new Response(), function ($stream) {
                return new Stream($stream);
            }),
        ]);
        $path = $request->getUri()->getPath();
        $params = $request->getQueryParams();
        $width = $params['w'] ?? 100;
        $height = $params['h'] ?? 100;
        $key = $this->config->getSecret();
        try {
            SignatureFactory::create($key)->validateRequest($basePath . $path, $params);
        } catch (SignatureException $e) {
            $this->placeholder($width, $height, 'aaaaaa', 'ffffff', $e->getMessage());
        }
        try {
            return $server->getImageResponse(urldecode($path), $params)
                ->withAddedHeader('pragma', 'public')
                ->withAddedHeader('X-Accel-Buffering', 'no');
        } catch (\Throwable $exception) {
            $this->placeholder($width, $height, 'aaaaaa', 'ffffff', $exception->getMessage());
        }
        return $handler->handle($request);
    }

    protected function placeholder($width, $height, $bg_color, $txt_color, $text = null)
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
        $fontsize = 12;
        imagettftext($image, $fontsize, 0, 0, ($height / 2) + ($fontsize * 0.2), $txt_color, __DIR__ . DIRECTORY_SEPARATOR . 'HelveticaNeue.ttf', $text);
        header("Content-Type: image/png");
        imagepng($image);
        imagedestroy($image);
        exit;
    }
}
