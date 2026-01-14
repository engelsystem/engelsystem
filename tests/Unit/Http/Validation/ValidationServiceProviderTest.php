<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Http\Validation;

use Engelsystem\Application;
use Engelsystem\Http\Validation\ValidationServiceProvider;
use Engelsystem\Http\Validation\Validator;
use Engelsystem\Test\Unit\Http\Validation\Stub\ValidatesRequestImplementation;
use Engelsystem\Test\Unit\ServiceProviderTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;
use stdClass;

#[CoversMethod(ValidationServiceProvider::class, 'register')]
class ValidationServiceProviderTest extends ServiceProviderTestCase
{
    public function testRegister(): void
    {
        $app = new Application();

        $serviceProvider = new ValidationServiceProvider($app);
        $serviceProvider->register();

        $this->assertTrue($app->has(Validator::class));
        $this->assertTrue($app->has('validator'));

        /** @var ValidatesRequestImplementation $validatesRequest */
        $validatesRequest = $app->make(ValidatesRequestImplementation::class);
        $this->assertTrue($validatesRequest->hasValidator());

        // Test afterResolving early return
        $app->make(stdClass::class);
    }
}
