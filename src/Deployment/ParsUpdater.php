<?php


namespace Pars\Core\Deployment;


use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\RequestOptions;
use Pars\Core\Container\ParsContainer;
use Pars\Core\Container\ParsContainerAwareTrait;
use Pars\Core\Database\Updater\AbstractDatabaseUpdater;
use Pars\Helper\String\StringHelper;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\UriInterface;

/**
 * Class ParsUpdater
 * @package Pars\Core\Deployment
 */
class ParsUpdater implements UpdaterInterface
{
    protected ContainerInterface $container;
    use ParsContainerAwareTrait;

    /**
     * ParsUpdater constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->setParsContainer($container->get(ParsContainer::class));
    }


    public function update()
    {
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
        $module = $this->getParsContainer()->getConfig()->get('update.module');
        $enabled = $this->getParsContainer()->getConfig()->get('update.enabled');
        if ($module && $enabled) {
            $this->getParsContainer()->getLogger()->info('UPDATE SELF');
            $cache = $this->container->get(CacheClearer::class);
            $cache->clear();
            $this->updateVersion($module);
            $cache->clear();
            try {
                $this->updateDB();
            } catch (\Throwable $exception) {
                $this->getParsContainer()->getLogger()->error('UPDATE DB ERROR', ['exception' => $exception]);
            }
            $cache->clear();
        }
    }

    protected function updateVersion(string $module)
    {
        $client = new Client();
        $response = $client->get("https://api.github.com/repos/PARS-Framework/$module/releases/latest",
            [
                RequestOptions::CONNECT_TIMEOUT => 20
            ]
        );

        $data = json_decode($response->getBody()->getContents(), true);
        $assets = array_filter($data['assets'], function ($asset) use ($module) {
            return StringHelper::startsWith($asset['name'], $module);
        });
        $asset = reset($assets);
        $download = $asset['browser_download_url'];
        $response = $client->get($download);
        if (PARS_VERSION != 'DEV' && PARS_VERSION != 'CORE') {
            $file = 'update.zip';
            file_put_contents($file, $response->getBody());
            $path = pathinfo(realpath($file), PATHINFO_DIRNAME);
            $zip = new \ZipArchive();
            $res = $zip->open($file);
            if ($res === TRUE) {
                $zip->extractTo($path);
                $zip->close();
            }
            unlink($file);
        }
    }

    /**
     * @param UriInterface $self
     */
    public function updateRemote(UriInterface $self)
    {
        $domains = $this->getParsContainer()->getConfig()->getDomainList();
        foreach ($domains as $domain) {
            $domainUri = new Uri($domain);
            if ($domainUri->getHost() == $self->getHost() && $domainUri->getPort() == $self->getPort()) {
                continue;
            }
            try {
                $domainUri = Uri::withQueryValue($domainUri, 'update', $this->getParsContainer()->getConfig()->getSecret(true));
                $domainUri = Uri::withQueryValue($domainUri, 'nopropagate', true);
                $client = new Client();
                $this->getParsContainer()->getLogger()->info('UPDATE: ' . $domainUri);
                $response = $client->get($domainUri);
                if ($response->getStatusCode() == 200) {
                    $this->getParsContainer()->getLogger()->info('UPDATE SUCCESS: ' . $domainUri);
                } else {
                    $this->getParsContainer()->getLogger()->info('UPDATE ERROR: ' . $domainUri);
                }
            } catch (\Throwable $exception) {
                $this->getParsContainer()->getLogger()->info('UPDATE ERROR: ' . $domainUri, ['exception' => $exception]);
            }
        }
    }

    protected function updateDB()
    {
        foreach ($this->getDbUpdaterList() as $dbUpdater) {
            try {
                $dbUpdater->executeSilent();
            } catch (\Throwable $exception) {
                $this->getParsContainer()->getLogger()->error('UPDATE DB ERROR', ['exception' => $exception]);
            }
        }
    }

    /**
     * @return AbstractDatabaseUpdater[]
     */
    public function getDbUpdaterList(): array
    {
        return [];
    }

}
