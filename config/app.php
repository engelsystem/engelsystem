<?php

// Application config

return [
    // Service providers
    'providers' => [
        \Engelsystem\Logger\LoggerServiceProvider::class,
        \Engelsystem\Exceptions\ExceptionsServiceProvider::class,
        \Engelsystem\Config\ConfigServiceProvider::class,
        \Engelsystem\Routing\RoutingServiceProvider::class,
        \Engelsystem\Renderer\RendererServiceProvider::class,
        \Engelsystem\Database\DatabaseServiceProvider::class,
        \Engelsystem\Http\RequestServiceProvider::class,
        \Engelsystem\Http\SessionServiceProvider::class,
        \Engelsystem\Http\ResponseServiceProvider::class,
        \Engelsystem\Http\Psr7ServiceProvider::class,
    ],
];
