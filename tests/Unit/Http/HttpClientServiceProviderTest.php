<?php

namespace Engelsystem\Test\Unit\Http;

use Engelsystem\Application;
use Engelsystem\Http\HttpClientServiceProvider;
use Engelsystem\Test\Unit\ServiceProviderTest;
use GuzzleHttp\Client as GuzzleClient;

class HttpClientServiceProviderTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Http\HttpClientServiceProvider::register
     */
    public function testRegister(): void
    {
        $app = new Application();

        $serviceProvider = new HttpClientServiceProvider($app);
        $serviceProvider->register();

        /** @var GuzzleClient $guzzle */
        $guzzle = $app->make(GuzzleClient::class);
        $config = $guzzle->getConfig();

        $this->assertFalse($config['http_errors']);
        $this->assertArrayHasKey('timeout', $config);
    }
}
