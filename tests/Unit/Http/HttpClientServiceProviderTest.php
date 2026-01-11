<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Http;

use Engelsystem\Application;
use Engelsystem\Http\HttpClientServiceProvider;
use Engelsystem\Test\Unit\ServiceProviderTestCase;
use GuzzleHttp\Client as GuzzleClient;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(HttpClientServiceProvider::class, 'register')]
class HttpClientServiceProviderTest extends ServiceProviderTestCase
{
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
