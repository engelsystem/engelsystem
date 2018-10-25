<?php

use FastRoute\RouteCollector;

/** @var RouteCollector $route */

// Pages
$route->get('/credits', 'CreditsController@index');

// Authentication
$route->get('/logout', 'AuthController@logout');

// Stats
$route->get('/metrics', 'Metrics\\Controller@metrics');
$route->get('/stats', 'Metrics\\Controller@stats');
