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
         *             'type' => 'string', # string, text, datetime-local, boolean, select, select_multi ...
         *             'default' => '[value]', # Optional
         *             'data' => ['[value]', '[key]' => '[value]'], # Optional, select data
         *             'required' => true, # Optional, default false
         *             'env' => '[name]', # Optional, env var to load, default name in upper case
         *             'hidden' => false, # Optional, default false, hides the config in frontend
         *             'permission' => '[permission]' # Optional, string or array
         *             # Optional translation: config.[name].info for information messages
         *             # Optionally other options used by the correlating field template
         *         ],
         *     ],
         * ],
         */

        'event' => [
            'config' => [
                'name' => [
                    'type' => 'string',
                ],
                'welcome_msg' => [
                    'type' => 'text',
                    'rows' => 5,
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
            ],
        ],

        'system' => [
            // Locale data is set based on /resources/lang/
            'config' => [
                'locales' => [
                    'type' => 'select_multi',
                    'data' => [],
                ],
                'default_locale' => [
                    'type' => 'select',
                    'data' => [],
                ],
            ],
        ],

        'goodie' => [
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
    ],
];
