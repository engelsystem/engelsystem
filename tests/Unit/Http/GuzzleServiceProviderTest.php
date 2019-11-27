<?php

namespace Engelsystem\Test\Unit\Http;

use Engelsystem\Application;
use Engelsystem\Http\GuzzleServiceProvider;
use Engelsystem\Test\Unit\ServiceProviderTest;
use GuzzleHttp\Client as GuzzleClient;

class GuzzleServiceProviderTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Http\GuzzleServiceProvider::register
     */
    public function testRegister()
    {
        $app = new Application();

        $serviceProvider = new GuzzleServiceProvider($app);
        $serviceProvider->register();

        /** @var GuzzleClient $guzzle */
        $guzzle = $app->make(GuzzleClient::class);
        $config = $guzzle->getConfig();

        $this->assertFalse($config['http_errors']);
        $this->assertArrayHasKey('timeout', $config);
    }
}
