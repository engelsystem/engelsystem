<?php

use Engelsystem\Application;
use Engelsystem\Config\Config;
use Engelsystem\Exceptions\Handler as ExceptionHandler;
use Engelsystem\Http\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

/**
 * This file includes all needed functions, connects to the db etc.
 */
require_once __DIR__ . '/autoload.php';


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
 * Initialize Request
 *
 * @var Request $request
 */
$request = Request::createFromGlobals();
$app->instance('request', $request);


/**
 * Include legacy code
 */
require __DIR__ . '/includes.php';


/**
 * Init application
 */
$sessionStorage = (PHP_SAPI != 'cli' ? new NativeSessionStorage(['cookie_httponly' => true]) : new MockArraySessionStorage());
$session = new Session($sessionStorage);
$app->instance('session', $session);
$session->start();
$request->setSession($session);


gettext_init();

load_auth();
