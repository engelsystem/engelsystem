<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers;

use Engelsystem\Application;
use Engelsystem\Helpers\Uuid;
use Engelsystem\Helpers\UuidServiceProvider;
use Engelsystem\Test\Unit\ServiceProviderTest;
use Illuminate\Support\Str;
use ReflectionProperty;

class UuidServiceProviderTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Helpers\UuidServiceProvider::register
     */
    public function testRegister(): void
    {
        $app = new Application();

        $serviceProvider = new UuidServiceProvider($app);
        $serviceProvider->register();

        $uuidFactoryReference = (new ReflectionProperty(Str::class, 'uuidFactory'))
            ->getValue();

        $this->assertIsCallable($uuidFactoryReference);
        $this->assertIsString($uuidFactoryReference);
        $this->assertEquals(Uuid::class . '::uuid', $uuidFactoryReference);

        $this->assertTrue(Str::isUuid(Str::uuid()), 'Is a UUID');
    }
}
