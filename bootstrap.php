<?php
require 'initialize.php';
$run = require 'static.php';
if ($run) {
    @include 'setup.php';
    (function () {
        $container = require PARS_DIR . '/config/container.php';
        $app = $container->getApplication();
        $app->run();
    })();
} else {
    return $run;
}
