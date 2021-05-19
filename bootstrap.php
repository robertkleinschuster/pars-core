<?php
define('PARS_DIR', getcwd());
if (PHP_SAPI === 'cli-server' && $_SERVER['SCRIPT_FILENAME'] !== __FILE__) {
    $decodedUri = urldecode($_SERVER['REQUEST_URI']);
    $uri = $_SERVER['REQUEST_URI'];
    if (count($_GET) == 0) {
        if (file_exists(PARS_DIR . "/public$decodedUri") || file_exists(PARS_DIR . "/public$uri")) {
            return false;
        }
    }
}
@include PARS_DIR . '/version.php';
@define('PARS_VERSION', 'CORE');
@include 'setup.php';
(function () {
    $container = require PARS_DIR . '/config/container.php';
    $app = $container->getApplication();
    $app->run();
})();
