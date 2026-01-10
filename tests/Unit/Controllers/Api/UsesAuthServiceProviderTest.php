<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Api;

use Engelsystem\Controllers\Api\UsesAuthServiceProvider;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\Controllers\Api\Stub\UsesAuthImplementation;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(UsesAuthServiceProvider::class, 'register')]
class UsesAuthServiceProviderTest extends TestCase
{
    public function testRegister(): void
    {
        $serviceProvider = new UsesAuthServiceProvider($this->app);
        $serviceProvider->register();

        $user = new User();

        $auth = $this->createMock(Authenticator::class);
        $this->setExpects($auth, 'user', null, $user);
        $this->app->instance(Authenticator::class, $auth);

        /** @var UsesAuthImplementation $instance */
        $instance = $this->app->make(UsesAuthImplementation::class);

        $this->assertEquals($user, $instance->user('self'));
    }
}
