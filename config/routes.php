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

// Password recovery
$route->get('/password/reset', 'PasswordResetController@reset');
$route->post('/password/reset', 'PasswordResetController@postReset');
$route->get('/password/reset/{token:.+}', 'PasswordResetController@resetPassword');
$route->post('/password/reset/{token:.+}', 'PasswordResetController@postResetPassword');

// Stats
$route->get('/metrics', 'Metrics\\Controller@metrics');
$route->get('/stats', 'Metrics\\Controller@stats');

// API
$route->get('/api/angeltypes/my', 'ApiController@getMyAngelTypes');
$route->get('/api/angeltypes', 'ApiController@getAngelTypes');
$route->get('/api/shifts/my', 'ApiController@getMyShifts');
$route->get('/api/shifts/free/{start:.+}/until/{stop:.+}', 'ApiController@getShiftsFree');
$route->get('/api/shifts/by/angeltype/{angeltypeid:.+}', 'ApiController@getShiftsByAngelType');
$route->get('/api[/{resource:.+}]', 'ApiController@index');
