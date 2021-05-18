<?php


namespace Pars\Core\Image;


use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Stream;
use League\Glide\Responses\PsrResponseFactory;
use League\Glide\Server;
use League\Glide\ServerFactory;
use League\Glide\Signatures\SignatureException;
use League\Glide\Signatures\SignatureFactory;
use League\Glide\Urls\UrlBuilderFactory;
use Pars\Core\Cache\ParsCache;
use Pars\Core\Config\ParsConfig;
use Pars\Helper\Filesystem\FilesystemHelper;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

class ImageProcessor
{
    use LoggerAwareTrait;

    protected ParsConfig $config;
    protected Server $glide;

    /**
     * ImageProcessor constructor.
     * @param ParsConfig $parsConfig
     * @param LoggerInterface $logger
     */
    public function __construct(ParsConfig $parsConfig, LoggerInterface $logger)
    {
        $this->setLogger($logger);
        $this->config = $parsConfig;
        $factory = new PsrResponseFactory(new Response(), function ($stream) {
            return new Stream($stream);
        });
        $config = $this->getConfig()->get('image');
        $config['response'] = $factory;
        $config['cache'] = FilesystemHelper::getPath('public' . $config['cache']);
        $config['source'] = FilesystemHelper::getPath('public' . $config['source']);
        $this->glide = ServerFactory::create($config);
    }

    /**
     * @return ParsConfig
     */
    public function getConfig(): ParsConfig
    {
        return $this->config;
    }

    /**
     * @return LoggerInterface|null
     */
    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }


    /**
     * @param string $path
     * @param array $params
     * @return string
     */
    public function buildUrl($path, $params = []): string
    {
        $cache = new ParsCache(__METHOD__, ParsCache::IMAGE_BASE_PATH);
        $cacheId = md5($path . implode($params));
        if (!$cache->has($cacheId)) {
            $domain = $this->getConfig()->get('asset.domain');
            $key = $this->getConfig()->getSecret();
            $calcBasePath = $this->getConfig()->get('image.path');
            $urlBuilder = UrlBuilderFactory::create($calcBasePath, $key);
            $calcUrl = $urlBuilder->getUrl($path, $params);
            $calcUrlWithDomain = "//" . $domain . $calcUrl;
            $client = new Client();
            try {
                $client->get($calcUrlWithDomain, [
                    RequestOptions::TIMEOUT => 2
                ]);
            } catch (\Throwable $exception) {
                $this->getLogger()->error($exception->getMessage(), ['exception' => $exception]);
                return $calcUrlWithDomain;
            }
            $cachePath = $this->glide->getCachePath($path, $params);
            $cacheBasePath = $this->getConfig()->get('image.cache');
            $cacheUrWithDomain = "//" . $domain . $cacheBasePath . '/' . $cachePath;
            $cache->set($cacheId, $cacheUrWithDomain);
        }
        return $cache->get($cacheId);
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
        $basePath = $this->getConfig()->get('image.path');
        $key = $this->getConfig()->getSecret();
        try {
            SignatureFactory::create($key)->validateRequest($basePath . $path, $params);
            $result = true;
        } catch (SignatureException $exception) {
            $this->getLogger()->error($exception->getMessage());
        }
        return $result;
    }

    public function getImageResponse(ServerRequestInterface $request)
    {
        $params = $request->getQueryParams();
        $path = $request->getUri()->getPath();
        $path = urldecode($path);
        $cacheFolder = $this->getConfig()->get('image.cache');
        try {
            FilesystemHelper::chmodRec("public$cacheFolder", 0755);
        } catch (\Throwable $exception) {
            $this->getLogger()->error($exception->getMessage(), ['exception' => $exception]);
        }
        return $this->glide->getImageResponse($path, $params)->withAddedHeader('pragma', 'public');
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
