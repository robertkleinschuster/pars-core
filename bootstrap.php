<?php
$GLOBALS['execTimeStart'] = microtime(true);
require 'initialize.php';
$run = require 'static.php';
if ($run) {
    @include 'setup.php';
    (function () {
        $container = require PARS_CONTAINER;
        $app = $container->getApplication();
        $app->run();
    })();
} else {
    return $run;
}
