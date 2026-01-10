<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Http;

use Engelsystem\Application;
use Engelsystem\Http\RedirectServiceProvider;
use Engelsystem\Test\Unit\ServiceProviderTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(RedirectServiceProvider::class, 'register')]
class RedirectServiceProviderTest extends ServiceProviderTestCase
{
    public function testRegister(): void
    {
        $app = new Application();

        $serviceProvider = new RedirectServiceProvider($app);
        $serviceProvider->register();

        $this->assertTrue($app->has('redirect'));
    }
}
