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
            $this->getParsContainer()->getLogger()->info('UPDATE SELF: ' . $module);
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

    /**
     * @param string $module
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function isNewAvailable(string $module): bool
    {
        $version = $this->getLatestVersionString($module);
        return $this->compareVersionString(PARS_VERSION, $version) > 0;
    }

    /**
     * @param string $module
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function updateVersion(string $module)
    {
        $download = $this->getLatestVersionDownload($module);

        if ($this->isNewAvailable($module) && $download) {
            $client = new Client();
            $response = $client->get($download);
            if (PARS_VERSION != 'DEV' && PARS_VERSION != 'CORE') {
                $file = 'update.zip';
                file_put_contents($file, $response->getBody());
                $path = dirname(realpath($file));
                $zip = new \ZipArchive();
                $res = $zip->open($file);
                if ($res === TRUE) {
                    $zip->extractTo($path);
                    $zip->close();
                }
                unlink($file);
            }
        }
    }

    /**
     * @param string $module
     * @return mixed|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getLatestVersionString(string $module)
    {
        $release = $this->findLatestRelease($module);
        return $release['version'] ?? '-';
    }

    /**
     * @param string $module
     * @return mixed|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getLatestVersionDownload(string $module)
    {
        $release = $this->findLatestRelease($module);
        return $release['download'] ?? null;
    }

    /**
     * @param string $current
     * @param string $latest
     * @return int
     */
    public function compareVersionString(string $current, string $latest): int
    {
        $current = (int)filter_var($current, FILTER_SANITIZE_NUMBER_INT);
        $latest = (int)filter_var($latest, FILTER_SANITIZE_NUMBER_INT);
        if ($current == 0 || $latest == 0) {
            return 0;
        }
        return $latest - $current;
    }


    /**
     * @param string $module
     * @return array|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function findLatestRelease(string $module): ?array
    {
        static $result = [];
        if (!isset($result[$module]) || $result[$module] === null) {
            $client = new Client();
            $response = $client->get("https://api.github.com/repos/PARS-Framework/$module/releases",
                [
                    RequestOptions::CONNECT_TIMEOUT => 20
                ]
            );

            $release_List = json_decode($response->getBody()->getContents(), true);
            foreach ($release_List as $release) {
                if ($this->acceptTag($release['tag_name'])) {
                    $assets = array_filter($release['assets'], function ($asset) use ($module) {
                        return StringHelper::startsWith($asset['name'], $module);
                    });
                    $asset = reset($assets);
                    $download = $asset['browser_download_url'];
                    $result[$module] = [
                        'download' => $download,
                        'version' => $release['tag_name']
                    ];
                    break;
                }
            }
            if ($result[$module] === null) {
                $result[$module] = false;
            }
        }
        return $result[$module] === false ? null : $result[$module];
    }

    protected function acceptTag(string $tag) {
        return StringHelper::contains($tag, PARS_BRANCH)
            || !StringHelper::endsWith($tag, '.0');
    }

    /**
     */
    public function updateRemote()
    {
        $try = 0;
        while ($try < 5 && $this->updateFrontend() === false) {
            $try++;
        }
        $try = 0;
        while ($try < 5 && $this->updateAdmin() === false) {
            $try++;
        }
    }

    protected function updateAdmin()
    {
        $domain = $this->getParsContainer()->getConfig()->getAssetDomain();
        return $this->updateByDomain($domain);
    }

    protected function updateFrontend()
    {
        $domain = $this->getParsContainer()->getConfig()->getFrontendDomain();
        return $this->updateByDomain($domain);
    }

    protected function updateByDomain(string $domain)
    {
        $domainUri = new Uri($domain);
        try {
            $domainUri = Uri::withQueryValue($domainUri, 'update', $this->getParsContainer()->getConfig()->getSecret(true));
            $domainUri = Uri::withQueryValue($domainUri, 'nopropagate', true);
            $client = new Client();
            $this->getParsContainer()->getLogger()->info('UPDATE: ' . $domainUri);
            $response = $client->get($domainUri, [
                RequestOptions::TIMEOUT => 300,
                RequestOptions::CONNECT_TIMEOUT => 300,
                RequestOptions::READ_TIMEOUT => 300,
            ]);
            if ($response->getStatusCode() == 200 && $response->hasHeader('update-success')) {
                $this->getParsContainer()->getLogger()->info('UPDATE SUCCESS: ' . $domainUri);
                return true;
            } else {
                $this->getParsContainer()->getLogger()->info('UPDATE ERROR: ' . $domainUri);
            }
            sleep(5);
        } catch (\Throwable $exception) {
            $this->getParsContainer()->getLogger()->info('UPDATE ERROR: ' . $domainUri, ['exception' => $exception]);
        }
        return false;
    }

    public function updateDB()
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
