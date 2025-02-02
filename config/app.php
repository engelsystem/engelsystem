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
];
