<?php

use FastRoute\RouteCollector;

/** @var RouteCollector $route */

// Pages
$route->get('/', 'HomeController@index');
$route->get('/credits', 'CreditsController@index');

// Authentication
$route->get('/login', 'AuthController@login');
$route->post('/login', 'AuthController@postLogin');
$route->get('/logout', 'AuthController@logout');

// Stats
$route->get('/metrics', 'Metrics\\Controller@metrics');
$route->get('/stats', 'Metrics\\Controller@stats');

// API
$route->get('/api[/{resource:.+}]', 'ApiController@index');
