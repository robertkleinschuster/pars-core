<?php
define('PARS_DIR', getcwd());
require PARS_DIR . '/vendor/autoload.php';
if (!@include PARS_DIR . '/version.php') {
    define('PARS_VERSION', 'CORE');
    define('PARS_BRANCH', 'master');
}

const PARS_CONTAINER = PARS_DIR . '/container.php';
const PARS_CACHE_DIR = PARS_DIR . '/data/cache';
const PARS_SESSION_DIR = PARS_DIR . '/data/session';
const PARS_CONFIG_CACHE = PARS_CACHE_DIR . '/config/config.php';
const PARS_DB_CONFIG = PARS_DIR . '/config/autoload/database.local.php';
const PARS_CONFIG_PATTERN = PARS_DIR . '/config/autoload/{{,*.}global,{,*.}local}.php';
const PARS_DEV_CONFIG = PARS_DIR . '/config/development.config.php';
const PARS_PUBLIC = PARS_DIR . '/public';
const PARS_TEMPLATE_DIR = PARS_DIR . '/templates';

if (!@include PARS_CONTAINER) {
    trigger_error('Container file not found. CWD: ' . PARS_DIR);
}
