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
    echo file_get_contents(__DIR__ . '/../resources/views/layouts/maintenance.html');
    die();
}


/**
 * Init authorization
 */
load_auth();
