<?php

chdir(dirname(__DIR__));
require 'vendor/autoload.php';

$server = League\Glide\ServerFactory::create([
    'source' => new \League\Flysystem\Filesystem(new \League\Flysystem\Adapter\Local($_SERVER['DOCUMENT_ROOT'] . '/upload')),
    'cache' => new \League\Flysystem\Filesystem(new \League\Flysystem\Adapter\Local($_SERVER['DOCUMENT_ROOT'] . '/cache')),
    'max_image_size' => 2000*2000,

]);
if (!isset($_GET['file'])) {
    return new \Laminas\Diactoros\Response\HtmlResponse('file parameter missing');
}
$path = str_replace('/upload', '', $_GET['file']);
try {
    \League\Glide\Signatures\SignatureFactory::create('pars-sign')->validateRequest('/img', $_GET);
} catch (\League\Glide\Signatures\SignatureException $e) {
    return new \Laminas\Diactoros\Response\HtmlResponse($e->getMessage());
}
$server->outputImage($path, $_GET);
exit;
