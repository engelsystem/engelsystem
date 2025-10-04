<?php

declare(strict_types=1);

// Application config

return [
    // Service providers
    'providers'  => [
        // Application bootstrap
        \Engelsystem\Logger\LoggerServiceProvider::class,
        \Engelsystem\Exceptions\ExceptionsServiceProvider::class,
        \Engelsystem\Config\ConfigServiceProvider::class,
        \Engelsystem\Helpers\ConfigureEnvironmentServiceProvider::class,
        \Engelsystem\Events\EventsServiceProvider::class,

        // Request handling
        \Engelsystem\Http\UrlGeneratorServiceProvider::class,
        \Engelsystem\Renderer\RendererServiceProvider::class,
        \Engelsystem\Database\DatabaseServiceProvider::class,
        \Engelsystem\Http\RequestServiceProvider::class,
        \Engelsystem\Http\SessionServiceProvider::class,
        \Engelsystem\Helpers\Translation\TranslationServiceProvider::class,
        \Engelsystem\Http\ResponseServiceProvider::class,
        \Engelsystem\Http\Psr7ServiceProvider::class,
        \Engelsystem\Helpers\CacheServiceProvider::class,
        \Engelsystem\Helpers\AuthenticatorServiceProvider::class,
        \Engelsystem\Helpers\AssetsServiceProvider::class,
        \Engelsystem\Renderer\TwigServiceProvider::class,
        \Engelsystem\Middleware\RouteDispatcherServiceProvider::class,
        \Engelsystem\Middleware\RequestHandlerServiceProvider::class,
        \Engelsystem\Http\Validation\ValidationServiceProvider::class,
        \Engelsystem\Http\RedirectServiceProvider::class,
        \Engelsystem\Http\PaginationServiceProvider::class,

        // Additional services
        \Engelsystem\Helpers\VersionServiceProvider::class,
        \Engelsystem\Mail\MailerServiceProvider::class,
        \Engelsystem\Http\HttpClientServiceProvider::class,
        \Engelsystem\Helpers\DumpServerServiceProvider::class,
        \Engelsystem\Helpers\UuidServiceProvider::class,
        \Engelsystem\Controllers\Api\UsesAuthServiceProvider::class,
    ],

    // Application middleware
    'middleware' => [
        // Basic initialization
        \Engelsystem\Middleware\SendResponseHandler::class,
        \Engelsystem\Middleware\ExceptionHandler::class,

        // Changes of request/response parameters
        \Engelsystem\Middleware\SetLocale::class,
        \Engelsystem\Middleware\ETagHandler::class,
        \Engelsystem\Middleware\AddHeaders::class,
        \Engelsystem\Middleware\TrimInput::class,

        // The application code
        \Engelsystem\Middleware\ErrorHandler::class,
        \Engelsystem\Middleware\ApiRouteHandler::class,
        \Engelsystem\Middleware\VerifyCsrfToken::class,
        \Engelsystem\Middleware\RouteDispatcher::class,
        \Engelsystem\Middleware\SessionHandler::class,

        // Handle request
        \Engelsystem\Middleware\RequestHandler::class,
    ],

    // Event handlers
    'event-handlers' => [
        // 'event' => [
        //      a list of
        //      'Class@method' or 'Class' (which uses @handle),
        //      ['Class', 'method'],
        //      callable like [$instance, 'method'] or 'function'
        //      or $function
        // ]

        'message.created' => \Engelsystem\Events\Listener\Messages::class . '@created',

        'news.created' => \Engelsystem\Events\Listener\News::class . '@created',
        'news.updated' => \Engelsystem\Events\Listener\News::class . '@updated',

        'oauth2.login' => \Engelsystem\Events\Listener\OAuth2::class . '@login',

        'shift.deleting' => [
            \Engelsystem\Events\Listener\Shifts::class . '@deletingCreateWorklogs',
            \Engelsystem\Events\Listener\Shifts::class . '@deletingSendEmails',
        ],

        'shift.updating' => \Engelsystem\Events\Listener\Shifts::class . '@updatedSendEmail',
    ],

    'config_options' => [
        /**
         * List of pages (key is the name/url)
         *
         * Structure of a config page:
         * '[key]' => [
         *   'title' => '[title]', # Optional, default to config.[key]
         *   'config' => [...], # The config options
         *   'validation' => callable, # Optional, callable($request, $rules) to validate the page request
         * ]
         *
         * Structure of a config option:
         * '[name]' => [
         *     'title' => '[title], # Optional, default config.[name]
         *     'permission' => '[permission]' # Optional, string or array
         *     'icon' => '[icon]', # Optional, default gear-fill
         *     'config' => [
         *         '[name]' => [ # Name must be globally unique
         *             'name' => 'some.value', # Optional, default: config.[name]
         *             'type' => 'string', # Possible types:
         *                  string, text, datetime-local, boolean, number, url, select, select_multi
         *             'default' => '[value]', # Optional
         *             'data' => ['[value]', '[key]' => '[value]'], # Optional, select data
         *             'required' => true, # Optional, default false
         *             'env' => '[name]', # Optional, env var to load, default name in upper case
         *             'hidden' => false, # Optional, default false, hides the config in frontend
         *             'permission' => '[permission]' # Optional, string or array
         *             'validation' => ['[validation]'] # Optional, array of validation options
         *             # Optional translation: config.[name].info for information messages
         *             # Optionally other options used by the correlating field template
         *         ],
         *     ],
         * ],
         */

        'event' => [
            'icon' => 'calendar-heart',
            'config' => [
                'name' => [
                    'type' => 'string',
                ],
                'welcome_msg' => [
                    'type' => 'text',
                    'rows' => 5,
                ],
                'registration_enabled' => [
                    'type' => 'boolean',
                    'default' => true,
                ],
                'buildup_start' => [
                    'type' => 'datetime-local',
                ],
                'event_start' => [
                    'type' => 'datetime-local',
                ],
                'event_end' => [
                    'type' => 'datetime-local',
                ],
                'teardown_end' => [
                    'type' => 'datetime-local',
                ],
                'enable_day_of_event' => [
                    'type' => 'boolean',
                    'default' => false,
                ],
                'event_has_day0' => [
                    'type' => 'boolean',
                    'default' => true,
                ],
                'faq_text' => [
                    'type' => 'text',
                ],
                'tshirt_link' => [
                    'type' => 'url',
                ],
                'privacy_email' => [
                    'type' => 'string',
                ],
            ],
        ],

        'features' => [
            'icon' => 'ui-checks',
            'config' => [
                'enable_dect' => [
                    'type' => 'boolean',
                    'default' => false,
                ],
                'enable_mobile_show' => [
                    'type' => 'boolean',
                    'default' => false,
                ],
                'enable_full_name' => [
                    'type' => 'boolean',
                    'default' => false,
                ],
                'display_full_name' => [
                    'type' => 'boolean',
                    'default' => false,
                ],
                'enable_pronoun' => [
                    'type' => 'boolean',
                    'default' => true,
                ],
                'enable_planned_arrival' => [
                    'type' => 'boolean',
                    'default' => true,
                ],
                'enable_force_active' => [
                    'type' => 'boolean',
                    'default' => true,
                ],
                'enable_voucher' => [
                    'type' => 'boolean',
                    'default' => true,
                ],
                'enable_force_food' => [
                    'type' => 'boolean',
                    'default' => false,
                ],
                'enable_self_worklog' => [
                    'type' => 'boolean',
                    'default' => true,
                ],
                'signup_requires_arrival' => [
                    'type' => 'boolean',
                    'default' => false,
                ],
                'autoarrive' => [
                    'type' => 'boolean',
                    'default' => false,
                ],
                'supporters_can_promote' => [
                    'type' => 'boolean',
                    'default' => false,
                ],
            ],
        ],

        'certificates' => [
            'icon' => 'card-checklist',
            'config' => [
                'driving_license_enabled' => [
                    'type' => 'boolean',
                    'default' => true,
                ],
                'ifsg_enabled' => [
                    'type' => 'boolean',
                    'default' => false,
                ],
                'ifsg_light_enabled' => [
                    'type' => 'boolean',
                    'default' => false,
                ],
            ],
        ],

        'shifts' => [
            'icon' => 'calendar-week',
            'config' => [
                'signup_advance_hours' => [
                    'type' => 'number',
                    'default' => 0,
                    'step' => .01,
                    'min' => 0,
                    'validation' => [
                        'min:0'
                    ],
                ],
                'signup_post_minutes' => [
                    'type' => 'number',
                    'default' => 0,
                    'step' => .01,
                ],
                'signup_post_fraction' => [
                    'type' => 'number',
                    'default' => 0,
                    'step' => .01,
                ],
                'last_unsubscribe' => [
                    'type' => 'number',
                    'default' => 3,
                    'step' => .01,
                    'min' => 0,
                    'validation' => [
                        'min:0'
                    ],
                ],
                'max_freeloadable_shifts' => [
                    'type' => 'number',
                    'default' => 2,
                ],
            ],
        ],

        'goodie' => [
            'icon' => 'gift',
            'config' => [
                'goodie_type' => [
                    'type' => 'select',
                    'default' => 'goodie',
                    'data' => [
                        'none',
                        'goodie',
                        'tshirt',
                    ],
                ],
                'enable_email_goodie' => [
                    'type' => 'boolean',
                    'default' => false,
                ],
            ],
        ],

        'system' => [
            'config' => [
                'app_name' => [
                    'type' => 'string',
                    'default' => 'Engelsystem',
                ],
                // Locale data is set based on /resources/lang/
                'locales' => [
                    'type' => 'select_multi',
                    'data' => [],
                ],
                'default_locale' => [
                    'type' => 'select',
                    'data' => [],
                ],
                'home_site' => [
                    'type' => 'select',
                    'default' => 'news',
                    'data' => [
                        'news' => '/news',
                        'meetings' => '/meetings',
                        'user-shifts' => '/user-shifts',
                        'angeltypes' => '/angeltypes',
                        'questions' => '/questions',
                    ],
                ],
                'display_news' => [
                    'type' => 'number',
                    'default' => 10,
                    'min' => 1,
                    'validation' => [
                        'int_val',
                        'min:1',
                    ],
                ],
                'filter_max_duration' => [
                    'type' => 'number',
                    'default' => 0,
                    'min' => 0,
                    'validation' => [
                        'int_val',
                        'min:0',
                    ],
                ],
                'password_min_length' => [
                    'type' => 'number',
                    'default' => 8,
                    'min' => 8,
                    'validation' => [
                        'int_val',
                        'min:8',
                    ],
                ],
                'enable_password' => [
                    'type' => 'boolean',
                    'default' => true,
                ],
                'external_registration_url' => [
                    'type' => 'url',
                ],
                'url' => [
                    'type' => 'url',
                    'env' => 'APP_URL',
                ],
                'api_key' => [
                    'type' => 'string',
                    'default' => '',
                ],

                // Hidden settings
                'maintenance' => [
                    'type' => 'boolean',
                    'hidden' => true,
                    'default' => false,
                ],
                'environment' => [
                    'type' => 'select',
                    'hidden' => true,
                    'default' => 'production',
                    'data' => ['production', 'development'],
                ],
                'documentation_url' => [
                    'type' => 'url',
                    'hidden' => true,
                    'default' => 'https://engelsystem.de/doc/',
                ],
                'setup_admin_password' => [
                    'type' => 'string',
                    'hidden' => true,
                ],
                'password_algorithm' => [
                    'type' => 'string',
                    'default' => PASSWORD_DEFAULT,
                    'hidden' => true,
                ],
                'username_regex' => [
                    'type' => 'string',
                    'default' => '/([^\p{L}\p{N}_.-]+)/ui',
                    'hidden' => true,
                ],
                'disabled_user_view_columns' => [
                    'type' => 'select',
                    'default' => [],
                    'hidden' => true,
                ],
                'trusted_proxies' => [
                    'type' => 'select_multi',
                    'add' => true,
                    'default' => ['127.0.0.0/8', '::ffff:127.0.0.0/8', '::1/128'],
                    'hidden' => true,
                ],
                'add_headers' => [
                    'type' => 'boolean',
                    'default' => true,
                    'hidden' => true,
                ],
            ],
        ],
    ],
];
