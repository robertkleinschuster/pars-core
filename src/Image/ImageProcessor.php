<?php


namespace Pars\Core\Image;


use League\Glide\Server;
use League\Glide\ServerFactory;
use League\Glide\Signatures\SignatureException;
use League\Glide\Signatures\SignatureFactory;
use Pars\Core\Container\ParsContainer;
use Pars\Core\Container\ParsContainerAwareTrait;
use Psr\Http\Message\ServerRequestInterface;

class ImageProcessor
{
    use ParsContainerAwareTrait;
    protected Server $glide;

    /**
     * ImageProcessor constructor.
     * @param Server $glide
     */
    public function __construct(ParsContainer $parsContainer)
    {
        $this->setParsContainer($parsContainer);
        $this->glide = ServerFactory::create($parsContainer->getConfig()->get('image'));
    }

    /**
     * @param ServerRequestInterface $request
     * @return bool
     */
    public function validateRequest(ServerRequestInterface $request): bool
    {
        $result = false;
        $params = $request->getQueryParams();
        $path = $request->getUri()->getPath();
        $basePath = $this->getParsContainer()->getConfig()->get('image.path');
        $key = $this->getParsContainer()->getConfig()->getSecret();
        try {
            SignatureFactory::create($key)->validateRequest($basePath . $path, $params);
            $result = true;
        } catch (SignatureException $exception) {
            $this->getParsContainer()->getLogger()->error($exception->getMessage());
        }
        return $result;
    }

    public function getImageResponse(ServerRequestInterface $request)
    {
        $params = $request->getQueryParams();
        $path = $request->getUri()->getPath();
        return $this->glide->getImageResponse(urldecode($path), $params)->withAddedHeader('pragma', 'public');
    }


    public function displayPlaceholder(ServerRequestInterface $request, $text = null)
    {
        $params = $request->getQueryParams();
        $width = $params['w'] ?? 100;
        $height = $params['h'] ?? 100;
        $bg_color = 'aaaaaa';
        $txt_color = 'ffffff';
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
