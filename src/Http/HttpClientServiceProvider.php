<?php

namespace Engelsystem\Http;

use Engelsystem\Container\ServiceProvider;
use GuzzleHttp\Client as GuzzleClient;

class HttpClientServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->when(GuzzleClient::class)
            ->needs('$config')
            ->give(
                function () {
                    return [
                        // No exception on >= 400 responses
                        'http_errors' => false,
                        // Wait max n seconds for a response
                        'timeout'     => 2.0,
                    ];
                }
            );
    }
}
