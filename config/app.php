<?php

// Application config

return [
    // Service providers
    'providers'  => [
        \Engelsystem\Logger\LoggerServiceProvider::class,
        \Engelsystem\Exceptions\ExceptionsServiceProvider::class,
        \Engelsystem\Config\ConfigServiceProvider::class,
        \Engelsystem\Http\UrlGeneratorServiceProvider::class,
        \Engelsystem\Renderer\RendererServiceProvider::class,
        \Engelsystem\Database\DatabaseServiceProvider::class,
        \Engelsystem\Http\RequestServiceProvider::class,
        \Engelsystem\Http\SessionServiceProvider::class,
        \Engelsystem\Http\ResponseServiceProvider::class,
        \Engelsystem\Http\Psr7ServiceProvider::class,
        \Engelsystem\Middleware\RouteDispatcherServiceProvider::class,
        \Engelsystem\Middleware\RequestHandlerServiceProvider::class,
    ],

    // Application middleware
    'middleware' => [
        \Engelsystem\Middleware\SendResponseHandler::class,
        \Engelsystem\Middleware\ExceptionHandler::class,
        \Engelsystem\Middleware\RouteDispatcher::class,
        \Engelsystem\Middleware\RequestHandler::class,
    ],
];
