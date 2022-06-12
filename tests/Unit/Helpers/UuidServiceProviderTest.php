<?php

namespace Engelsystem\Test\Unit\Helpers;

use Engelsystem\Application;
use Engelsystem\Helpers\UuidServiceProvider;
use Engelsystem\Test\Unit\ServiceProviderTest;
use Illuminate\Support\Str;

class UuidServiceProviderTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Helpers\UuidServiceProvider::register
     * @covers \Engelsystem\Helpers\UuidServiceProvider::uuid
     */
    public function testRegister()
    {
        $app = new Application();

        $serviceProvider = new UuidServiceProvider($app);
        $serviceProvider->register();

        $this->assertStringMatchesFormat(
            '%x%x%x%x%x%x%x%x-%x%x%x%x-4%x%x%x-%x%x%x%x-%x%x%x%x%x%x%x%x%x%x%x%x',
            Str::uuid()
        );
    }
}
