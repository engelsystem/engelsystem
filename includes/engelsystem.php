<?php

/**
 * Bootstrap application
 */

use Engelsystem\Http\UrlGeneratorInterface;

require __DIR__ . '/application.php';


/**
 * Include legacy code
 */
require __DIR__ . '/includes.php';


/**
 * Check for maintenance
 */
/** @var \Engelsystem\Application $app */
if ($app->get('config')->get('maintenance')) {
    http_response_code(503);
    $url = $app->get(UrlGeneratorInterface::class);
    $maintenance = file_get_contents(__DIR__ . '/../resources/views/layouts/maintenance.html');
    $maintenance = str_replace('%APP_NAME%', $app->get('config')->get('app_name'), $maintenance);
    $maintenance = str_replace('%ASSETS_PATH%', $url->to(''), $maintenance);
    echo $maintenance;
    die();
}
