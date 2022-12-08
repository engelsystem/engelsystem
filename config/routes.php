<?php

use FastRoute\RouteCollector;

/** @var RouteCollector $route */

// Pages
$route->get('/', 'HomeController@index');
$route->get('/credits', 'CreditsController@index');
$route->get('/health', 'HealthController@index');

// Authentication
$route->get('/login', 'AuthController@login');
$route->post('/login', 'AuthController@postLogin');
$route->get('/logout', 'AuthController@logout');

// OAuth
$route->addGroup(
    '/oauth/{provider}',
    function (RouteCollector $route) {
        $route->get('', 'OAuthController@index');
        $route->post('/connect', 'OAuthController@connect');
        $route->post('/disconnect', 'OAuthController@disconnect');
    }
);

// User settings
$route->addGroup(
    '/settings',
    function (RouteCollector $route) {
        $route->get('/profile', 'SettingsController@profile');
        $route->post('/profile', 'SettingsController@saveProfile');
        $route->get('/password', 'SettingsController@password');
        $route->post('/password', 'SettingsController@savePassword');
        $route->get('/theme', 'SettingsController@theme');
        $route->post('/theme', 'SettingsController@saveTheme');
        $route->get('/language', 'SettingsController@language');
        $route->post('/language', 'SettingsController@saveLanguage');
        $route->get('/oauth', 'SettingsController@oauth');
    }
);

// Password recovery
$route->addGroup(
    '/password/reset',
    function (RouteCollector $route) {
        $route->get('', 'PasswordResetController@reset');
        $route->post('', 'PasswordResetController@postReset');
        $route->get('/{token:.+}', 'PasswordResetController@resetPassword');
        $route->post('/{token:.+}', 'PasswordResetController@postResetPassword');
    }
);

// Stats
$route->get('/metrics', 'Metrics\\Controller@metrics');
$route->get('/stats', 'Metrics\\Controller@stats');

// News
$route->get('/meetings', 'NewsController@meetings');
$route->addGroup(
    '/news',
    function (RouteCollector $route) {
        $route->get('', 'NewsController@index');
        $route->get('/{id:\d+}', 'NewsController@show');
        $route->post('/{id:\d+}', 'NewsController@comment');
        $route->post('/comment/{id:\d+}', 'NewsController@deleteComment');
    }
);

// FAQ
$route->get('/faq', 'FaqController@index');

// Questions
$route->addGroup(
    '/questions',
    function (RouteCollector $route) {
        $route->get('', 'QuestionsController@index');
        $route->post('', 'QuestionsController@delete');
        $route->get('/new', 'QuestionsController@add');
        $route->post('/new', 'QuestionsController@save');
    }
);

// Messages
$route->addGroup(
    '/messages',
    function (RouteCollector $route) {
        $route->get('', 'MessagesController@index');
        $route->post('', 'MessagesController@redirectToConversation');
        $route->get('/{user_id:\d+}', 'MessagesController@messagesOfConversation');
        $route->post('/{user_id:\d+}', 'MessagesController@send');
        $route->post('/{user_id:\d+}/{msg_id:\d+}', 'MessagesController@delete');
    }
);

// API
$route->get('/api[/{resource:.+}]', 'ApiController@index');

// Design
$route->get('/design', 'DesignController@index');

// Administration
$route->addGroup(
    '/admin',
    function (RouteCollector $route) {
        // FAQ
        $route->addGroup(
            '/faq',
            function (RouteCollector $route) {
                $route->get('[/{id:\d+}]', 'Admin\\FaqController@edit');
                $route->post('[/{id:\d+}]', 'Admin\\FaqController@save');
            }
        );

        // Log
        $route->addGroup(
            '/logs',
            function (RouteCollector $route) {
                $route->get('', 'Admin\\LogsController@index');
                $route->post('', 'Admin\\LogsController@index');
            }
        );

        // Schedule
        $route->addGroup(
            '/schedule',
            function (RouteCollector $route) {
                $route->get('', 'Admin\\Schedule\\ImportSchedule@index');
                $route->get('/edit[/{id:\d+}]', 'Admin\\Schedule\\ImportSchedule@edit');
                $route->post('/edit[/{id:\d+}]', 'Admin\\Schedule\\ImportSchedule@save');
                $route->get('/load/{id:\d+}', 'Admin\\Schedule\\ImportSchedule@loadSchedule');
                $route->post('/import/{id:\d+}', 'Admin\\Schedule\\ImportSchedule@importSchedule');
            }
        );

        // Questions
        $route->addGroup(
            '/questions',
            function (RouteCollector $route) {
                $route->get('', 'Admin\\QuestionsController@index');
                $route->post('', 'Admin\\QuestionsController@delete');
                $route->get('/{id:\d+}', 'Admin\\QuestionsController@edit');
                $route->post('/{id:\d+}', 'Admin\\QuestionsController@save');
            }
        );

        // User
        $route->addGroup(
            '/user/{id:\d+}',
            function (RouteCollector $route) {
                // Shirts
                $route->addGroup(
                    '/shirt',
                    function (RouteCollector $route) {
                        $route->get('', 'Admin\\UserShirtController@editShirt');
                        $route->post('', 'Admin\\UserShirtController@saveShirt');
                    }
                );

                // Worklogs
                $route->addGroup(
                    '/worklog',
                    function (RouteCollector $route) {
                        $route->get('[/{worklog_id:\d+}]', 'Admin\\UserWorkLogController@editWorklog');
                        $route->post('[/{worklog_id:\d+}]', 'Admin\\UserWorkLogController@saveWorklog');
                        $route->get(
                            '/{worklog_id:\d+}/delete',
                            'Admin\\UserWorkLogController@showDeleteWorklog'
                        );
                        $route->post(
                            '/{worklog_id:\d+}/delete',
                            'Admin\\UserWorkLogController@deleteWorklog'
                        );
                    }
                );
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
