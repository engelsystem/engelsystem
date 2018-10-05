<?php

/**
 * Bootstrap application
 */
require __DIR__ . '/application.php';


/**
 * Include legacy code
 */
require __DIR__ . '/includes.php';


/**
 * Check for maintenance
 */
if ($app->get('config')->get('maintenance')) {
    $maintenance = file_get_contents(__DIR__ . '/../resources/views/layouts/maintenance.html');
    $maintenance = str_replace('%APP_NAME%', $app->get('config')->get('app_name'), $maintenance);
    echo $maintenance;
    die();
}


/**
 * Init authorization
 */
load_auth();
