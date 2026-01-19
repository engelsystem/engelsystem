<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Http;

use Engelsystem\Application;
use Engelsystem\Http\PaginationServiceProvider;
use Engelsystem\Http\Request;
use Engelsystem\Test\Unit\ServiceProviderTestCase;
use Illuminate\Pagination\Paginator;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(PaginationServiceProvider::class, 'register')]
class PaginationServiceProviderTest extends ServiceProviderTestCase
{
    public function testRegister(): void
    {
        $app = new Application();

        $serviceProvider = new PaginationServiceProvider($app);
        $serviceProvider->register();

        $app->instance('request', new Request());

        $this->assertTrue(Paginator::resolveCurrentPath('/default') != '/default');
    }
}
