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

// News
$route->get('/news', 'NewsController@index');
$route->get('/meetings', 'NewsController@meetings');
$route->get('/news/{id:\d+}', 'NewsController@show');
$route->post('/news/{id:\d+}', 'NewsController@comment');

// API
$route->get('/api[/{resource:.+}]', 'ApiController@index');

// Design
$route->get('/design', 'DesignController@index');

// Administration
$route->addGroup(
    '/admin',
    function (RouteCollector $route) {
        // Log
        $route->get('/logs', 'Admin\\LogsController@index');
        $route->post('/logs', 'Admin\\LogsController@index');

        // Schedule
        $route->addGroup(
            '/schedule',
            function (RouteCollector $route) {
                $route->get('', 'Admin\\Schedule\\ImportSchedule@index');
                $route->post('/load', 'Admin\\Schedule\\ImportSchedule@loadSchedule');
                $route->post('/import', 'Admin\\Schedule\\ImportSchedule@importSchedule');
            }
        );

        // News
        $route->addGroup(
            '/news',
            function (RouteCollector $route) {
                $route->get('[/{id:\d+}]', 'Admin\\NewsController@edit');
                $route->post('[/{id:\d+}]', 'Admin\\NewsController@save');
            }
        );
    }
);
