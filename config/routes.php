<?php

declare(strict_types=1);

use FastRoute\RouteCollector;

/** @var RouteCollector $route */

// Pages
$route->get('/', 'HomeController@index');
$route->get('/register', 'RegistrationController@view');
$route->post('/register', 'RegistrationController@save');
$route->get('/credits', 'CreditsController@index');
$route->get('/health', 'HealthController@index');

// Authentication
$route->get('/login', 'AuthController@login');
$route->post('/login', 'AuthController@postLogin');
$route->get('/logout', 'AuthController@logout');

// OAuth
$route->addGroup(
    '/oauth/{provider}',
    function (RouteCollector $route): void {
        $route->get('', 'OAuthController@index');
        $route->post('/connect', 'OAuthController@connect');
        $route->post('/disconnect', 'OAuthController@disconnect');
    }
);

// User settings
$route->addGroup(
    '/settings',
    function (RouteCollector $route): void {
        $route->get('/profile', 'SettingsController@profile');
        $route->post('/profile', 'SettingsController@saveProfile');
        $route->get('/password', 'SettingsController@password');
        $route->post('/password', 'SettingsController@savePassword');
        $route->get('/theme', 'SettingsController@theme');
        $route->post('/theme', 'SettingsController@saveTheme');
        $route->get('/language', 'SettingsController@language');
        $route->post('/language', 'SettingsController@saveLanguage');
        $route->get('/certificates', 'SettingsController@certificate');
        $route->post('/certificates/ifsg', 'SettingsController@saveIfsgCertificate');
        $route->post('/certificates/driving', 'SettingsController@saveDrivingLicense');
        $route->get('/api', 'SettingsController@api');
        $route->post('/api', 'SettingsController@apiKeyReset');
        $route->get('/oauth', 'SettingsController@oauth');
        $route->get('/sessions', 'SettingsController@sessions');
        $route->post('/sessions', 'SettingsController@sessionsDelete');
    }
);

// User admin settings
$route->addGroup(
    '/users/{user_id:\d+}',
    function (RouteCollector $route): void {
            $route->get('/certificates', 'Admin\\UserSettingsController@certificate');
            $route->post('/certificates/ifsg', 'Admin\\UserSettingsController@saveIfsgCertificate');
            $route->post('/certificates/driving', 'Admin\\UserSettingsController@saveDrivingLicense');
    }
);

// Password recovery
$route->addGroup(
    '/password/reset',
    function (RouteCollector $route): void {
        $route->get('', 'PasswordResetController@reset');
        $route->post('', 'PasswordResetController@postReset');
        $route->get('/{token:.+}', 'PasswordResetController@resetPassword');
        $route->post('/{token:.+}', 'PasswordResetController@postResetPassword');
    }
);

// Stats
$route->get('/metrics', 'Metrics\\Controller@metrics');
$route->get('/stats', 'Metrics\\Controller@stats');

// Angeltypes
$route->addGroup('/angeltypes', function (RouteCollector $route): void {
    $route->get('/about', 'AngelTypesController@about');
});

// Shifts
$route->addGroup('/shifts', function (RouteCollector $route): void {
    $route->get('/random', 'ShiftsController@random');
});

// News
$route->get('/meetings', 'NewsController@meetings');
$route->addGroup(
    '/news',
    function (RouteCollector $route): void {
        $route->get('', 'NewsController@index');
        $route->get('/{news_id:\d+}', 'NewsController@show');
        $route->post('/{news_id:\d+}', 'NewsController@comment');
        $route->post('/comment/{comment_id:\d+}', 'NewsController@deleteComment');
    }
);

// FAQ
$route->get('/faq', 'FaqController@index');

// Questions
$route->addGroup(
    '/questions',
    function (RouteCollector $route): void {
        $route->get('', 'QuestionsController@index');
        $route->post('', 'QuestionsController@delete');
        $route->get('/new', 'QuestionsController@add');
        $route->post('/new', 'QuestionsController@save');
    }
);

// Messages
$route->addGroup(
    '/messages',
    function (RouteCollector $route): void {
        $route->get('', 'MessagesController@index');
        $route->post('', 'MessagesController@redirectToConversation');
        $route->get('/{user_id:\d+}', 'MessagesController@messagesOfConversation');
        $route->post('/{user_id:\d+}', 'MessagesController@send');
        $route->post('/{user_id:\d+}/{msg_id:\d+}', 'MessagesController@delete');
    }
);

// API
$route->addGroup(
    '/api',
    function (RouteCollector $route): void {
        $route->get('', 'Api\IndexController@index');

        $route->addGroup(
            '/v0-beta',
            function (RouteCollector $route): void {
                $route->addRoute(['OPTIONS'], '[/{resource:.+}]', 'Api\IndexController@options');
                $route->get('', 'Api\IndexController@indexV0');
                $route->get('/openapi', 'Api\IndexController@openApiV0');
                $route->get('/info', 'Api\IndexController@info');

                $route->get('/angeltypes', 'Api\AngelTypeController@index');
                $route->get('/angeltypes/{angeltype_id:\d+}/shifts', 'Api\ShiftsController@entriesByAngeltype');

                $route->get('/locations', 'Api\LocationsController@index');
                $route->get('/locations/{location_id:\d+}/shifts', 'Api\ShiftsController@entriesByLocation');

                $route->get('/news', 'Api\NewsController@index');

                $route->get('/shifttypes', 'Api\ShiftTypeController@index');
                $route->get('/shifttypes/{shifttype_id:\d+}/shifts', 'Api\ShiftsController@entriesByShiftType');

                $route->get('/users/{user_id:(?:\d+|self)}', 'Api\UsersController@user');
                $route->get('/users/{user_id:(?:\d+|self)}/angeltypes', 'Api\AngelTypeController@ofUser');
                $route->get('/users/{user_id:(?:\d+|self)}/shifts', 'Api\ShiftsController@entriesByUser');

                $route->addRoute(
                    ['POST', 'PUT', 'DELETE', 'PATCH'],
                    '/[{resource:.+}]',
                    'Api\IndexController@notImplemented'
                );
                $route->get('/[{resource:.+}]', 'Api\IndexController@notFound');
            }
        );
        $route->get('/[{resource:.+}]', 'Api\IndexController@notFound');
    }
);

// Feeds
$route->get('/atom', 'FeedController@atom');
$route->get('/ical', 'FeedController@ical');
$route->get('/rss', 'FeedController@rss');
$route->get('/shifts-json-export', 'FeedController@shifts');

// Design
$route->get('/design', 'DesignController@index');

// Administration
$route->addGroup(
    '/admin',
    function (RouteCollector $route): void {
        // FAQ
        $route->addGroup(
            '/faq',
            function (RouteCollector $route): void {
                $route->get('[/{faq_id:\d+}]', 'Admin\\FaqController@edit');
                $route->post('[/{faq_id:\d+}]', 'Admin\\FaqController@save');
            }
        );

        // Log
        $route->addGroup(
            '/logs',
            function (RouteCollector $route): void {
                $route->get('', 'Admin\\LogsController@index');
                $route->post('', 'Admin\\LogsController@index');
            }
        );

        // Schedule
        $route->addGroup(
            '/schedule',
            function (RouteCollector $route): void {
                $route->get('', 'Admin\\ScheduleController@index');
                $route->get('/edit[/{schedule_id:\d+}]', 'Admin\\ScheduleController@edit');
                $route->post('/edit[/{schedule_id:\d+}]', 'Admin\\ScheduleController@save');
                $route->get('/load/{schedule_id:\d+}', 'Admin\\ScheduleController@loadSchedule');
                $route->post('/import/{schedule_id:\d+}', 'Admin\\ScheduleController@importSchedule');
            }
        );

        // Shifts
        $route->addGroup(
            '/shifts',
            function (RouteCollector $route): void {
                $route->get('/history', 'Admin\\ShiftsController@history');
                $route->post('/history', 'Admin\\ShiftsController@deleteTransaction');
            }
        );

        // Shift types
        $route->addGroup(
            '/shifttypes',
            function (RouteCollector $route): void {
                $route->get('', 'Admin\\ShiftTypesController@index');
                $route->post('', 'Admin\\ShiftTypesController@delete');
                $route->get('/{shift_type_id:\d+}', 'Admin\\ShiftTypesController@view');
                $route->get('/edit[/{shift_type_id:\d+}]', 'Admin\\ShiftTypesController@edit');
                $route->post('/edit[/{shift_type_id:\d+}]', 'Admin\\ShiftTypesController@save');
            }
        );

        // Questions
        $route->addGroup(
            '/questions',
            function (RouteCollector $route): void {
                $route->get('', 'Admin\\QuestionsController@index');
                $route->post('', 'Admin\\QuestionsController@delete');
                $route->get('/{question_id:\d+}', 'Admin\\QuestionsController@edit');
                $route->post('/{question_id:\d+}', 'Admin\\QuestionsController@save');
            }
        );

        // Locations
        $route->addGroup(
            '/locations',
            function (RouteCollector $route): void {
                $route->get('', 'Admin\\LocationsController@index');
                $route->post('', 'Admin\\LocationsController@delete');
                $route->get('/edit[/{location_id:\d+}]', 'Admin\\LocationsController@edit');
                $route->post('/edit[/{location_id:\d+}]', 'Admin\\LocationsController@save');
            }
        );

        // User
        $route->addGroup(
            '/user/{user_id:\d+}',
            function (RouteCollector $route): void {
                // Goodies
                $route->addGroup(
                    '/goodie',
                    function (RouteCollector $route): void {
                        $route->get('', 'Admin\\UserGoodieController@editGoodie');
                        $route->post('', 'Admin\\UserGoodieController@saveGoodie');
                    }
                );

                // Worklogs
                $route->addGroup(
                    '/worklog',
                    function (RouteCollector $route): void {
                        $route->get('[/{worklog_id:\d+}]', 'Admin\\UserWorklogController@editWorklog');
                        $route->post('[/{worklog_id:\d+}]', 'Admin\\UserWorklogController@saveWorklog');
                        $route->get(
                            '/{worklog_id:\d+}/delete',
                            'Admin\\UserWorklogController@showDeleteWorklog'
                        );
                        $route->post(
                            '/{worklog_id:\d+}/delete',
                            'Admin\\UserWorklogController@deleteWorklog'
                        );
                    }
                );
            }
        );

        // News
        $route->addGroup(
            '/news',
            function (RouteCollector $route): void {
                $route->get('[/{news_id:\d+}]', 'Admin\\NewsController@edit');
                $route->post('[/{news_id:\d+}]', 'Admin\\NewsController@save');
            }
        );
    }
);
