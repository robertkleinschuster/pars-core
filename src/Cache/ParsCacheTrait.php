<?php


namespace Pars\Core\Cache;


use Pars\Helper\Filesystem\FilesystemHelper;

trait ParsCacheTrait
{
    private static string $pathsKey = 'paths';
    private static string $pathsFileName = 'pars-path-cache';
    private static string $pathsBasePath = 'data/cache/paths/';

    protected function savePath(string $basePath)
    {
        if ($basePath == self::$pathsBasePath) {
            return;
        }
        $cache = new ParsCache(self::$pathsFileName, self::$pathsBasePath);
        $paths = $cache->get(self::$pathsKey, []);
        if (!in_array($basePath, $paths)) {
            $paths[] = $basePath;
            $cache->set(self::$pathsKey, $paths);
        }
    }

    public static function clearAll()
    {
        $cache = new ParsCache(self::$pathsFileName, self::$pathsBasePath);
        $paths = $cache->get(self::$pathsKey, []);
        foreach ($paths as $path) {
            FilesystemHelper::deleteDirectory($path);
        }
        $cache->clear();
    }
}
