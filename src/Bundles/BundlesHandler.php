<?php


namespace Pars\Core\Bundles;


use JShrink\Minifier;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\HtmlResponse;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use PHPUnit\TextUI\XmlConfiguration\Logging\TestDox\Html;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class BundlesHandler implements RequestHandlerInterface
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

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $filesystem = new Filesystem(new Local($this->config['root']));
        $bundle = $request->getAttribute('bundle');
        if ($bundle == 'clear') {
            $files = $filesystem->listContents('out');
            foreach ($files as $file) {
                $bundle .= $filesystem->get($file['path'])->delete();
            }
            return new HtmlResponse('OK');
        }

        if ($bundle == 'js') {
            $bundleName = 'out/bundle_' . date('Y-m-d') . '.js';
            if (!$filesystem->has($bundleName)) {
                $files = $filesystem->listContents('js');
                $bundle = '';
                foreach ($files as $file) {
                    $bundle .= PHP_EOL . $filesystem->get($file['path'])->read();
                }
                if (isset($this->config['sources']['js'])) {
                    foreach ($this->config['sources']['js'] as $source) {
                        $f = new Filesystem(new Local($source));
                        $fs = $f->listContents();
                        foreach ($fs as $fi) {
                            $bundle .= PHP_EOL . $f->get($fi['path'])->read();
                        }
                    }
                }
                $bundle = Minifier::minify($bundle, array('flaggedComments' => false));
                $filesystem->put($bundleName, $bundle);
            }
            $file = $filesystem->get($bundleName);
            return (new Response($file->readStream()))->withHeader('Content-Type', 'application/x-javascript');
        }

        if ($bundle == 'css') {
            $bundleName = 'out/bundle_' . date('Y-m-d') . '.css';
            if (!$filesystem->has($bundleName)) {
                $files = $filesystem->listContents('css');
                $bundle = '';
                foreach ($files as $file) {
                    $bundle .= $filesystem->get($file['path'])->read();
                }
                if (isset($this->config['sources']['css'])) {
                    foreach ($this->config['sources']['css'] as $source) {
                        $f = new Filesystem(new Local($source));
                        $fs = $f->listContents();
                        foreach ($fs as $fi) {
                            $bundle .= $f->get($fi['path'])->read();
                        }
                    }
                }
                $filesystem->put($bundleName, $bundle);
            }
            $file = $filesystem->get($bundleName);
            return (new Response($file->readStream()))->withHeader('Content-Type', "text/css");
        }
        return new Response('');
    }

    public static function getRoute()
    {
        return '/bundles/{bundle}';
    }

}
