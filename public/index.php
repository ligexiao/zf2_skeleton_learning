<?php
/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */

ini_set('display_errors', 'On');
ini_set('apc.cache_by_default', 0);
error_reporting(-1);

chdir(dirname(__DIR__));

// Decline static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server') {
    $path = realpath(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    if (__FILE__ !== $path && is_file($path)) {
        return false;
    }
    unset($path);
}

// Setup autoloading
require 'init_autoloader.php';

define('ENV', get_cfg_var('runtime.environment') ?: 'development');
define('ROOT_PATH', dirname(__DIR__));
date_default_timezone_set('PRC');
if (ENV == 'production') {// 线上关闭error report
    error_reporting(0);
}

require __DIR__ . '/../module/App.php';

// Run the application!
$application = Zend\Mvc\Application::init(require 'config/application.config.php');
App::set($application);
$application->run();