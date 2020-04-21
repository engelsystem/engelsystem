<?php

use Engelsystem\Application;
use Engelsystem\Config\Config;
use Engelsystem\Exceptions\Handler;
use Engelsystem\Exceptions\Handlers\HandlerInterface;


/**
 * Include the autoloader
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
 * Configure environment
 */
if (config('environment') == 'development') {
    $errorHandler = $app->get('error.handler');
    $errorHandler->setEnvironment(Handler::ENV_DEVELOPMENT);
    $app->bind(HandlerInterface::class, 'error.handler.development');
}
