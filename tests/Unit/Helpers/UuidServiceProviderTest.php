<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers;

use Engelsystem\Application;
use Engelsystem\Helpers\Uuid;
use Engelsystem\Helpers\UuidServiceProvider;
use Engelsystem\Test\Unit\ServiceProviderTestCase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\CoversMethod;
use ReflectionProperty;

#[CoversMethod(UuidServiceProvider::class, 'register')]
class UuidServiceProviderTest extends ServiceProviderTestCase
{
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
