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
$route->addGroup('/api/v2019-alpha', function (RouteCollector $route) {
    $route->get('/angeltypes/my', 'ApiController@getMyAngelTypes');
    $route->get('/angeltypes', 'ApiController@getAngelTypes');
    $route->get('/shifts/my', 'ApiController@getMyShifts');
    $route->get('/shifts/free/{start:.+}/until/{stop:.+}', 'ApiController@getShiftsFree');
    $route->get('/shifts/by/angeltype/{angeltypeid:\d+}', 'ApiController@getShiftsByAngelType');
    $route->get('[/{resource:.+}]', 'ApiController@index');
});
