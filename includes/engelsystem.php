<?php

use Engelsystem\Application;
use Engelsystem\Config\Config;
use Engelsystem\Exceptions\BasicHandler as ExceptionHandler;

/**
 * This file includes all needed functions, connects to the db etc.
 */
require_once __DIR__ . '/autoload.php';


/**
 * Include legacy code
 */
require __DIR__ . '/includes.php';


/**
 * Initialize and bootstrap the application
 */
$app = new Application(realpath(__DIR__ . DIRECTORY_SEPARATOR . '..'));
$appConfig = $app->make(Config::class);
$appConfig->set(require config_path('app.php'));
$app->bootstrap($appConfig);


/**
 * Configure application
 */
date_default_timezone_set($app->get('config')->get('timezone'));

if (config('environment') == 'development') {
    $errorHandler = $app->get('error.handler');
    $errorHandler->setEnvironment(ExceptionHandler::ENV_DEVELOPMENT);
    ini_set('display_errors', true);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', false);
}


/**
 * Check for maintenance
 */
if ($app->get('config')->get('maintenance')) {
    echo file_get_contents(__DIR__ . '/../templates/maintenance.html');
    die();
}


/**
 * Init translations
 */
gettext_init();


/**
 * Init authorization
 */
load_auth();
