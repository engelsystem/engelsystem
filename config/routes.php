<?php

use Engelsystem\Http\Exceptions\HttpTemporaryRedirect;
use FastRoute\RouteCollector;

/** @var RouteCollector $route */

// Pages
$route->get('/', function () {
    throw new HttpTemporaryRedirect(auth()->user() ? config('home_site') : 'login');
});
$route->get('/credits', 'CreditsController@index');

// Authentication
$route->get('/logout', 'AuthController@logout');

// Stats
$route->get('/metrics', 'Metrics\\Controller@metrics');
$route->get('/stats', 'Metrics\\Controller@stats');

// API
$route->get('/api[/{resource:.+}]', 'ApiController@index');
