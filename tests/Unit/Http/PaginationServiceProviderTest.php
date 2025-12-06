<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Http;

use Engelsystem\Application;
use Engelsystem\Http\PaginationServiceProvider;
use Engelsystem\Http\Request;
use Engelsystem\Test\Unit\ServiceProviderTest;
use Illuminate\Pagination\Paginator;

class PaginationServiceProviderTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Http\PaginationServiceProvider::register
     */
    public function testRegister(): void
    {
        $app = new Application();

        $serviceProvider = new PaginationServiceProvider($app);
        $serviceProvider->register();

        $app->instance('request', new Request());

        $this->assertTrue(Paginator::resolveCurrentPath('/default') != '/default');
    }
}
