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
    echo file_get_contents(__DIR__ . '/../templates/layouts/maintenance.html');
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
