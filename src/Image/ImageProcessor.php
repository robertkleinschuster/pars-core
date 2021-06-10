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
    protected ParsCache $cache;

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
        $config['cache'] = FilesystemHelper::createPath('public' . $config['cache']);
        $config['source'] = FilesystemHelper::createPath('public' . $config['source']);
        $this->glide = ServerFactory::create($config);
        $this->cache = new ParsCache('image-processor', ParsCache::IMAGE_BASE_PATH);

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
        if (!isset($params['fit'])) {
            $params['fit'] = 'crop';
        }
        $domain = $this->getConfig()->get('asset.domain');
        $key = $this->getConfig()->getSecret();
        $calcBasePath = $this->getConfig()->get('image.path');
        $urlBuilder = UrlBuilderFactory::create($calcBasePath, $key);
        $calcUrl = $urlBuilder->getUrl($path, $params);
        $calcUrlWithDomain = $domain . $calcUrl;
        return '//' . $calcUrlWithDomain;
        $cacheId = md5($calcUrlWithDomain);
        if (!$this->cache->has($cacheId)) {
            $client = new Client();
            try {
                $client->get($calcUrlWithDomain, [
                    RequestOptions::TIMEOUT => 0,
                    RequestOptions::CONNECT_TIMEOUT => 0,
                    RequestOptions::READ_TIMEOUT => 0,
                ]);
                $cachePath = $this->glide->getCachePath($path, $params);
                $cacheBasePath = $this->getConfig()->get('image.cache');
                $cacheUrWithDomain = "//" . $domain . $cacheBasePath . '/' . $cachePath;
            } catch (\Throwable $exception) {
                $this->getLogger()->error($exception->getMessage(), ['exception' => $exception]);
                $cacheUrWithDomain = '//' . $calcUrlWithDomain;
            }
            $this->cache->set($cacheId, $cacheUrWithDomain);
            $this->cache->commit();
        }
        return $this->cache->get($cacheId);
    }

    /**
     * @param $path
     * @param $params
     * @param ...$p
     */
    public function buildHtml($path, $params, ...$p)
    {
        static $count = 0;
        $count++;
        $param = $params;
        $attributes = [];
        if (!is_array($params)) {
            $params = [];
        }
        if (is_integer($param)) {
            $params['w'] = $param;
        } else if (is_string($param)) {
            $params['alt'] = $param;
        }
        if (count($p) > 0) {
            if (is_string($p[0]) && !isset($params['alt'])) {
                $params['alt'] = $p[0];
                unset($p[0]);
            } elseif (is_integer($p[0]) && is_integer($param)) {
                $params['h'] = $p[0];
                unset($p[0]);
                if (isset($p[1]) && is_string($p[1])) {
                    $params['alt'] = $p[1];
                    unset($p[1]);
                }
            } elseif (is_integer($p[0]) && is_string($param)) {
                $params['w'] = $p[0];
                unset($p[0]);
                if (isset($p[1]) && is_integer($p[1])) {
                    $params['h'] = $p[1];
                    unset($p[1]);
                }
            }
            $params['class'] = '';
            while (count($p)) {
                $pop = array_pop($p);
                if (is_string($pop)) {
                    $params['class'] .= ' ' . $pop;
                } elseif (is_array($pop)) {
                    $attributes += $pop;
                }
            }
        }
        $attr = '';
        foreach ($attributes as $key => $val) {
            $attr .= " $key='$val'";
        }

        if (!isset($params['w'])) {
            $params['w'] = 351;
        }
        $loading = $params['loading'] ?? $count > 1 ? 'lazy' : 'eager';
        $widthSmall = $params['w'];
        $heightSmall = $params['h'] ?? null;
        $widthMedium = $widthSmall * 2;
        $heightMedium = $heightSmall * 1.7;
        $widthLarge = $widthSmall * 3;
        $heightLarge = $heightSmall * 2;
        if (!isset($params['fit'])) {
            $params['fit'] = 'crop';
        }
        $alt = $params['alt'] ?? '';
        $class = $params['class'] ?? '';
        // Small
        // Jpeg
        $params['fm'] = 'jpg';
        $params['dpr'] = 1;
        $small = $this->buildUrl($path, $params);
        $params['dpr'] = 2;
        $smallX2 = $this->buildUrl($path, $params);
        // Webp
        $params['fm'] = 'webp';
        $params['dpr'] = 1;
        $smallWebP = $this->buildUrl($path, $params);
        $params['dpr'] = 2;
        $smallX2WebP = $this->buildUrl($path, $params);
        // Medium
        // Jpeg
        $params['fm'] = 'jpg';
        $params['w'] = $widthMedium;
        $params['h'] = $heightMedium;
        $params['dpr'] = 1;
        $medium = $this->buildUrl($path, $params);
        $params['dpr'] = 2;
        $mediumX2 = $this->buildUrl($path, $params);
        // Webp
        $params['fm'] = 'webp';
        $params['dpr'] = 1;
        $mediumWebP = $this->buildUrl($path, $params);
        $params['dpr'] = 2;
        $mediumX2WebP = $this->buildUrl($path, $params);
        // Large
        // Jpeg
        $params['fm'] = 'jpg';
        $params['w'] = $widthLarge;
        $params['h'] = $heightLarge;
        $params['dpr'] = 1;
        $large = $this->buildUrl($path, $params);
        $params['dpr'] = 2;
        $largeX2 = $this->buildUrl($path, $params);
        // Webp
        $params['fm'] = 'webp';
        $params['dpr'] = 1;
        $largeWebP = $this->buildUrl($path, $params);
        $params['dpr'] = 2;
        $largeX2WebP = $this->buildUrl($path, $params);
        $result = '';
        if ($loading == 'lazy') {
            $result .= "<noscript class='loading-lazy'>";
        }
        $result .= "<picture class='$class' data-original='{$this->buildUrl($path)}'>";
        $result .= "<source media='(min-width: {$widthLarge}px)' srcset='$largeWebP 1x, $largeX2WebP 2x' type='image/webp'>";
        $result .= "<source media='(min-width: {$widthLarge}px)' srcset='$large 1x, $largeX2 2x'>";

        $result .= "<source media='(min-width: {$widthMedium}px)' srcset='$mediumWebP 1x, $mediumX2WebP 2x' type='image/webp'>";
        $result .= "<source media='(min-width: {$widthMedium}px)' srcset='$medium 1x, $mediumX2 2x'>";

        $result .= "<source srcset='$smallWebP 1x, $smallX2WebP 2x' type='image/webp'>";
        $result .= "<source srcset='$small 1x, $smallX2 2x'>";

        $result .= "<img $attr loading='lazy' width='$widthSmall' height='$heightSmall' class='img' src='$small' alt='$alt'>";
        $result .= "</picture>";
        if ($loading == 'lazy') {
            $result .= "</noscript>";
        }
        return $result;
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
