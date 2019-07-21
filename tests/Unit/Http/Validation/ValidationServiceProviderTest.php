<?php

namespace Engelsystem\Test\Unit\Http\Validation;

use Engelsystem\Application;
use Engelsystem\Http\Validation\ValidationServiceProvider;
use Engelsystem\Http\Validation\Validator;
use Engelsystem\Test\Unit\Http\Validation\Stub\ValidatesRequestImplementation;
use Engelsystem\Test\Unit\ServiceProviderTest;
use stdClass;

class ValidationServiceProviderTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Http\Validation\ValidationServiceProvider::register
     */
    public function testRegister()
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
