<?php
if (PHP_SAPI === 'cli-server' && $_SERVER['SCRIPT_FILENAME'] !== __FILE__) {
    $decodedUri = urldecode($_SERVER['REQUEST_URI']);
    $uri = $_SERVER['REQUEST_URI'];
    if (count($_GET) == 0) {
        $file = PARS_DIR . "/public$decodedUri";
        if (file_exists($file) && is_file($file)) {
            return false;
        }
        $file = PARS_DIR . "/public$uri";
        if (file_exists($file) && is_file($file)) {
            return false;
        }
    }
}
return true;
