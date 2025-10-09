<?php

declare(strict_types=1);

namespace Engelsystem\Http;

use Engelsystem\Application;
use Engelsystem\Container\ServiceProvider;
use GuzzleHttp\Client as GuzzleClient;

class HttpClientServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->when(GuzzleClient::class)
            ->needs('$config')
            ->give(
                function (Application $app) {
                    return [
                        // No exception on >= 400 responses
                        'http_errors' => false,
                        // Wait max n seconds for a response
                        'timeout'     => $app->get('config')->get('guzzle_timeout'),
                    ];
                }
            );
    }
}
