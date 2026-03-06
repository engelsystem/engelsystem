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

        'user.created' => \Engelsystem\Events\Listener\Users::class . '@created',
    ],

    'config_options' => [
        /**
         * List of pages (key is the page name (url slug))
         *
         * Structure of a config page:
         * '[slug]' => [
         *   'title' => '[title]', # Optional page title, default to config.[key]
         *   'validation' => callable, # Optional, callable($request, $rules) to validate the page request
         *    'config' => [...], # A list of "config options" / config fields, see below
         * ]
         *
         * Structure of a config option:
         * '[name]' => [ # Config name
         *     'title' => '[title], # Optional, default config.[name]
         *     'permission' => '[permission]' # Optional, string or array
         *     'icon' => '[icon]', # Optional, default gear-fill
         *     'config' => [
         *         '[name]' => [ # Name must be globally unique
         *             'name' => 'some.value', # Optional, default: config.[name]
         *             'info' => 'some.info', # Optional, default: config.[name].info if available
         *             'type' => 'string', # Possible types:
         *                  string, text, datetime-local, date, boolean, number, url, select, select_multi
         *             'default' => '[value]', # Optional
         *             'data' => ['[value]', '[key]' => '[value]'], # Optional, select data
         *             'required' => true, # Optional, default false
         *             'env' => '[name]', # Optional, env var to load, default name in upper case
         *             'hidden' => false, # Optional, default false, hides the config from frontend
         *             'permission' => '[permission]' # Optional, string or array
         *             'validation' => ['[validation]'] # Optional, array of validation options
         *             'write_back' => false, # Optional, writes the config to config.local.php
         *                                                Only effective for single-server-installations
         *             'preserve_key' => false, # Optional, preserves key in selects, disables auto translation
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
                'privacy_email' => [
                    'type' => 'string',
                ],
            ],
        ],

        'features' => [
            'icon' => 'ui-checks',
            'config' => [
                'enable_dect' => [
                    'name' => 'general.dect',
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
                'required_user_fields' => [
                    'type' => 'select_multi',
                    'data' => [
                        'pronoun' => 'settings.profile.pronoun',
                        'firstname' => 'settings.profile.firstname',
                        'lastname' => 'settings.profile.lastname',
                        'tshirt_size' => 'user.shirt_size',
                        'mobile' => 'settings.profile.mobile',
                        'dect' => 'general.dect',
                    ],
                    'default' => [
                        'tshirt_size',
                    ],
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
                'voucher_settings.initial_vouchers' => [
                    'type' => 'number',
                    'default' => 0,
                    'env' => 'INITIAL_VOUCHERS',
                ],
                'voucher_settings.shifts_per_voucher' => [
                    'type' => 'number',
                    'default' => 0,
                    'env' => 'SHIFTS_PER_VOUCHER',
                ],
                'voucher_settings.hours_per_voucher' => [
                    'type' => 'number',
                    'default' => 2,
                    'env' => 'HOURS_PER_VOUCHER',
                ],
                'voucher_settings.voucher_start' => [
                    'type' => 'date',
                    'env' => 'VOUCHER_START',
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
                'join_qr_code' => [
                    'type' => 'boolean',
                    'default' => true,
                ],
            ],
        ],

        'certificates' => [
            'icon' => 'card-checklist',
            'config' => [
                'driving_license_enabled' => [
                    'name' => 'settings.certificates.driving_license',
                    'type' => 'boolean',
                    'default' => true,
                ],
                'ifsg_enabled' => [
                    'name' => 'ifsg.certificate',
                    'type' => 'boolean',
                    'default' => false,
                ],
                'ifsg_light_enabled' => [
                    'name' => 'ifsg.certificate_light',
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
                        'min:0',
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
                        'min:0',
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
                'tshirt_link' => [
                    'type' => 'url',
                ],
                'night_shifts.enabled' => [
                    'type' => 'boolean',
                    'default' => true,
                    'env' => 'NIGHT_SHIFTS',
                ],
                'night_shifts.start' => [
                    'type' => 'number',
                    'default' => 2,
                    'env' => 'NIGHT_SHIFTS_START',
                ],
                'night_shifts.end' => [
                    'type' => 'number',
                    'default' => 8,
                    'env' => 'NIGHT_SHIFTS_END',
                ],
                'night_shifts.multiplier' => [
                    'type' => 'number',
                    'default' => 2,
                    'env' => 'NIGHT_SHIFTS_MULTIPLIER',
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
                    'required' => true,
                    'data' => [],
                ],
                'default_locale' => [
                    'type' => 'select',
                    'required' => true,
                    'data' => [],
                ],
                'theme' => [
                    'type' => 'select',
                    'required' => true,
                    // Data is set based on available themes
                    'data' => [],
                    'preserve_key' => true,
                ],
                'timezone' => [
                    'type' => 'select',
                    'required' => true,
                    // Timezone data is set based on PHP config
                    'data' => [],
                    'default' => 'Europe/Berlin',
                    'write_back' => true,
                ],
                'app_key' => [
                    'type' => 'password',
                    'default' => '',
                    'min_length' => 32,
                    'validation' => [
                        'length:32',
                    ],
                ],
                'database.driver' => [
                    'type' => 'select',
                    'required' => true,
                    'default' => 'mysql',
                    'data' => [
                        'mysql',
                        'mariadb',
                    ],
                    'env' => 'MYSQL_TYPE',
                    'write_back' => true,
                ],
                'database.host' => [
                    'type' => 'string',
                    'required' => true,
                    'default' => 'localhost',
                    'env' => 'MYSQL_HOST',
                    'write_back' => true,
                ],
                'database.database' => [
                    'type' => 'string',
                    'required' => true,
                    'default' => 'engelsystem',
                    'env' => 'MYSQL_DATABASE',
                    'write_back' => true,
                ],
                'database.username' => [
                    'type' => 'string',
                    'required' => true,
                    'default' => 'root',
                    'env' => 'MYSQL_USER',
                    'write_back' => true,
                ],
                'database.password' => [
                    'type' => 'password',
                    'required' => true,
                    'default' => '',
                    'env' => 'MYSQL_PASSWORD',
                    'write_back' => true,
                ],
                'email.driver' => [
                    'type' => 'select',
                    'required' => true,
                    'default' => 'mail',
                    'env' => 'MAIL_DRIVER',
                    'data' => [
                        'smtp',
                        'sendmail',
                        'mail',
                        'log',
                    ],
                ],
                'email.from.name' => [
                    'type' => 'string',
                    'default' => 'Engelsystem',
                    'env' => 'MAIL_FROM_NAME',
                ],
                'email.from.address' => [
                    'type' => 'email',
                    'required' => true,
                    'default' => 'noreply@example.com',
                    'env' => 'MAIL_FROM_ADDRESS',
                ],
                'email.host' => [
                    'type' => 'string',
                    'default' => 'localhost',
                    'env' => 'MAIL_HOST',
                ],
                'email.port' => [
                    'type' => 'number',
                    'default' => 465,
                    'env' => 'MAIL_PORT',
                    'min' => 1,
                    'max' => 65535,
                    'validation' => [
                        'int_val',
                        'min:1',
                        'max:65535',
                    ],
                ],
                'email.tls' => [
                    'type' => 'boolean',
                    'env' => 'MAIL_TLS',
                    'default' => true,
                ],
                'email.username' => [
                    'type' => 'string',
                    'env' => 'MAIL_USERNAME',
                ],
                'email.password' => [
                    'type' => 'password',
                    'env' => 'MAIL_PASSWORD',
                ],
                'email.sendmail' => [
                    'type' => 'string',
                    'hidden' => true,
                    'default' => '/usr/sbin/sendmail -bs',
                    'env' => 'MAIL_SENDMAIL',
                ],
                'home_site' => [
                    'type' => 'select',
                    'required' => true,
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
                    'required' => true,
                    'default' => 10,
                    'min' => 1,
                    'validation' => [
                        'int_val',
                        'min:1',
                    ],
                ],
                'display_users' => [
                    'type' => 'number',
                    'required' => true,
                    'default' => 100,
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
                    'required' => true,
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
                    'write_back' => true,
                ],
                'api_key' => [
                    'type' => 'string',
                    'default' => '',
                ],
                'session.driver' => [
                    'type' => 'select',
                    'required' => true,
                    'default' => 'pdo',
                    'env' => 'SESSION_DRIVER',
                    'warning' => 'config.warning_logout',
                    'write_back' => true,
                    'data' => [
                        'pdo',
                        'native',
                    ],
                ],
                'session.name' => [
                    'type' => 'string',
                    'required' => true,
                    'default' => 'session',
                    'env' => 'SESSION_NAME',
                    'warning' => 'config.warning_logout',
                    'write_back' => true,
                ],
                'session.lifetime' => [
                    'type' => 'number',
                    'required' => true,
                    'default' => 30,
                    'env' => 'SESSION_LIFETIME',
                    'write_back' => true,
                ],
                'jwt_expiration_time' => [
                    'type' => 'number',
                    'required' => true,
                    'default' => 60 * 24 * 7,
                    'min' => 1,
                    'validation' => [
                        'int_val',
                        'min:1',
                    ],
                ],
                'guzzle_timeout' => [
                    'type' => 'number',
                    'required' => true,
                    'default' => 2.0,
                    'min' => .01,
                    'step' => .01,
                    'validation' => [
                        'min:.01',
                    ],
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
                    'required' => true,
                    'default' => 'production',
                    'data' => ['production', 'development'],
                    'write_back' => true,
                ],
                'header_items' => [
                    'type' => 'static',
                    'default' => [
                        // Name can be a translation string, permission is an engelsystem privilege
                        // %lang% will be replaced with the users language
                        // 'Name' => 'URL',
                        // 'some.key' => ['URL', 'permission'],
                        //'Foo' => ['https://foo.bar/batz-%lang%.html', 'logout'], // Permission: for logged-in users
                    ],
                    'hidden' => true,
                ],
                'footer_items' => [
                    'type' => 'static',
                    'default' => [
                        // Name can be a translation string, permission is an engelsystem privilege
                        // 'Name' => 'URL',
                        // 'some.key' => ['URL', 'permission'],

                        // URL to faq page
                        'faq.faq' => ['/faq', 'faq.view'],

                        // Contact email address, linked on every page
                        //'Contact' => 'mailto:ticket@c3heaven.de',
                    ],
                    'hidden' => true,
                ],
                'contact_options' => [
                    // Multiple contact options / links are possible, analogue to footer_items
                    'type' => 'static',
                    'default' => [
                        // E-mail address
                        //'general.email' => 'mailto:ticket@c3heaven.de',
                    ],
                    'hidden' => true,
                ],
                'documentation_url' => [
                    'type' => 'url',
                    'default' => 'https://engelsystem.de/doc/',
                    'hidden' => true,
                ],
                'credits' => [
                    'type' => 'static',
                    'name' => 'credits.title',
                    'default' => [
                        'Contribution' => 'Please visit '
                            . '[engelsystem/engelsystem](https://github.com/engelsystem/engelsystem) if '
                            . 'you want to contribute, have found any '
                            . '[bugs](https://github.com/engelsystem/engelsystem/issues) or need help.',
                    ],
                    'hidden' => true,
                ],
                'setup_admin_password' => [
                    'type' => 'string',
                    'hidden' => true,
                ],
                'password_algorithm' => [
                    'type' => 'string',
                    'required' => true,
                    'default' => PASSWORD_DEFAULT,
                    'hidden' => true,
                ],
                'username_regex' => [
                    'type' => 'string',
                    'required' => true,
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
                    'write_back' => true,
                ],
                'headers' => [
                    'type' => 'static',
                    'default' => [
                        'X-Content-Type-Options'  => 'nosniff',
                        'X-Frame-Options'         => 'sameorigin',
                        'Referrer-Policy'         => 'strict-origin-when-cross-origin',
                        'Content-Security-Policy' =>
                            'default-src \'self\'; '
                            . ' style-src \'self\' \'unsafe-inline\'; '
                            . 'img-src \'self\' data:;',
                        'X-XSS-Protection'        => '1; mode=block',
                        'Feature-Policy'          => 'autoplay \'none\'',
                        //'Strict-Transport-Security' => 'max-age=7776000',
                        //'Expect-CT' => 'max-age=7776000,enforce,report-uri="[uri]"',
                    ],
                    'hidden' => true,
                ],
                'add_headers' => [
                    'type' => 'boolean',
                    'default' => true,
                    'hidden' => true,
                ],
                'oauth' => [
                    'type' => 'static',
                    'default' => [
                        // '[name]' => [config]
                        /*
                        '[name]' => [
                            // Name shown to the user (optional)
                            'name' => 'Some Provider',
                            // Auth client ID
                            'client_id' => 'engelsystem',
                            // Auth client secret
                            'client_secret' => '[generated by provider]',
                            // Authentication URL
                            'url_auth' => '[generated by provider]',
                            // Token URL
                            'url_token' => '[generated by provider]',
                            // User info URL which provides userdata
                            'url_info' => '[generated by provider]',
                            // OAuth Scopes
                            // 'scope' => ['openid'],
                            // Info unique user id field
                            'id' => 'uuid',
                            // The following fields are used for registration
                            // Info username field (optional)
                            'username' => 'nickname',
                            // Info email field (optional)
                            'email' => 'email',
                            // Info first name field (optional)
                            'first_name' => 'first-name',
                            // Info last name field (optional)
                            'last_name' => 'last-name',
                            // User URL to provider, linked on provider settings page (optional)
                            'url' => '[provider page]',
                            // Whether info attributes are nested arrays (optional)
                            // For example {"user":{"name":"foo"}} can be accessed using user.name
                            'nested_info' => false,
                            // Only show after clicking the page title (optional)
                            'hidden' => false,
                            // Mark user as arrived when using this provider (optional)
                            'mark_arrived' => false,
                            // If the password field should be enabled on registration (optional)
                            'enable_password' => false,
                            // Allow registration even if disabled in config (optional)
                            'allow_registration' => null,
                            // Allow disconnecting user accounts from the oauth provider (optional)
                            'allow_user_disconnect' => true,
                            // Auto join teams
                            // Info groups field (optional)
                            'groups' => 'groups',
                            // Groups to team (angel type) mapping (optional)
                            'teams' => [
                                '/Lorem' => 4, // 4 being the ID of the team (angel type)
                                '/Foo Mod' => ['id' => 5, 'supporter' => true], // 5 = ID of the team (angel type)
                            ],
                        ],
                        */
                    ],
                    'hidden' => true,
                ],
                'tshirt_sizes' => [
                    'type' => 'static',
                    'default' => [
                        'S'    => 'Small Straight-Cut',
                        'S-F'  => 'Small Fitted-Cut',
                        'M'    => 'Medium Straight-Cut',
                        'M-F'  => 'Medium Fitted-Cut',
                        'L'    => 'Large Straight-Cut',
                        'L-F'  => 'Large Fitted-Cut',
                        'XL'   => 'XLarge Straight-Cut',
                        'XL-F' => 'XLarge Fitted-Cut',
                        '2XL'  => '2XLarge Straight-Cut',
                        '3XL'  => '3XLarge Straight-Cut',
                        '4XL'  => '4XLarge Straight-Cut',
                    ],
                    'hidden' => true,
                ],
                'themes' => [
                    'type' => 'static',
                    'default' => [
                        // Index 1 loads theme1.scss etc.
                        21 => [
                            'name' => 'Engelsystem 39c3 (2025)',
                            'type' => 'dark',
                            'navbar_classes' => 'navbar-dark',
                        ],
                        20 => [
                            'name' => 'Engelsystem eh22-light (2025)',
                            'type' => 'light',
                            'navbar_classes' => 'navbar-light',
                        ],
                        19 => [
                            'name' => 'Engelsystem eh22-dark (2025)',
                            'type' => 'dark',
                            'navbar_classes' => 'navbar-dark',
                        ],
                        18 => [
                            'name' => 'Engelsystem 38c3 (2024) - Lila, Lachs und Kurven',
                            'type' => 'dark',
                            'navbar_classes' => 'navbar-dark',
                        ],
                        17 => [
                            'name' => 'Engelsystem 37c3 (2023)',
                            'type' => 'dark',
                            'navbar_classes' => 'navbar-dark',
                        ],
                        16 => [
                            'name' => 'Engelsystem cccamp23 (2023)',
                            'type' => 'dark',
                            'navbar_classes' => 'navbar-dark',
                        ],
                        15 => [
                            'name' => 'Engelsystem rC3 (2021)',
                            'type' => 'dark',
                            'navbar_classes' => 'navbar-dark',
                        ],
                        14 => [
                            'name' => 'Engelsystem rC3 teal (2020)',
                            'type' => 'dark',
                            'navbar_classes' => 'navbar-dark bg-black border-dark',
                        ],
                        13 => [
                            'name' => 'Engelsystem rC3 violet (2020)',
                            'type' => 'dark',
                            'navbar_classes' => 'navbar-dark bg-black border-dark',
                        ],
                        12 => [
                            'name' => 'Engelsystem 36c3 (2019)',
                            'type' => 'dark',
                            'navbar_classes' => 'navbar-dark bg-black border-dark',
                        ],
                        10 => [
                            'name' => 'Engelsystem cccamp19 green (2019)',
                            'type' => 'dark',
                            'navbar_classes' => 'navbar-dark bg-black border-dark',
                        ],
                        9 => [
                            'name' => 'Engelsystem cccamp19 yellow (2019)',
                            'type' => 'dark',
                            'navbar_classes' => 'navbar-dark bg-black border-dark',
                        ],
                        8 => [
                            'name' => 'Engelsystem cccamp19 blue (2019)',
                            'type' => 'dark',
                            'navbar_classes' => 'navbar-dark bg-black border-dark',
                        ],
                        7 => [
                            'name' => 'Engelsystem 35c3 dark (2018)',
                            'type' => 'dark',
                            'navbar_classes' => 'navbar-primary navbar-dark bg-black border-primary',
                        ],
                        6 => [
                            'name' => 'Engelsystem 34c3 dark (2017)',
                            'type' => 'dark',
                            'navbar_classes' => 'navbar-dark bg-black border-dark',
                        ],
                        5 => [
                            'name' => 'Engelsystem 34c3 light (2017)',
                            'type' => 'light',
                            'navbar_classes' => 'navbar-light bg-light',
                        ],
                        4 => [
                            'name' => 'Engelsystem 33c3 (2016)',
                            'type' => 'dark',
                            'navbar_classes' => 'navbar-dark bg-body border-dark',
                        ],
                        3 => [
                            'name' => 'Engelsystem 32c3 (2015)',
                            'type' => 'light',
                            'navbar_classes' => 'navbar-dark bg-black border-dark',
                        ],
                        2 => [
                            'name' => 'Engelsystem cccamp15',
                            'type' => 'light',
                            'navbar_classes' => 'navbar-light bg-light',
                        ],
                        22 => [
                            'name' => 'Engelsystem Pro',
                            'type' => 'light',
                            'navbar_classes' => 'navbar-light',
                        ],
                        11 => [
                            'name' => 'Engelsystem high contrast',
                            'type' => 'dark',
                            'navbar_classes' => 'navbar-dark bg-black border-dark',
                        ],
                        0 => [
                            'name' => 'Engelsystem light',
                            'type' => 'light',
                            'navbar_classes' => 'navbar-light bg-light',
                        ],
                        1 => [
                            'name' => 'Engelsystem dark',
                            'type' => 'dark',
                            'navbar_classes' => 'navbar-dark bg-black border-dark',
                        ],
                    ],
                    'hidden' => true,
                ],
                'jwt_algorithm' => [
                    'type' => 'select',
                    'required' => true,
                    // see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40
                    // and firebase/php-jwt/src/JWT.php
                    'default' => 'HS256',
                    'data' => ['HS256'],
                    'hidden' => true,
                ],
                'metrics' => [
                    'type' => 'static',
                    'default' => [
                        // User work buckets in seconds
                        'work'    => [
                            1 * 60 * 60, (int) (1.5 * 60 * 60), 2 * 60 * 60, 3 * 60 * 60,
                            5 * 60 * 60, 10 * 60 * 60, 20 * 60 * 60,
                        ],
                        'voucher' => [0, 1, 2, 3, 5, 10, 15, 20],
                    ],
                    'hidden' => true,
                ],
                'var_dump_server' => [
                    'type' => 'static',
                    'default' => [
                        'host' => '127.0.0.1',
                        'port' => '9912',
                        'enable' => false,
                    ],
                    'hidden' => true,
                    'write_back' => true,
                ],
            ],
        ],
    ],
];
