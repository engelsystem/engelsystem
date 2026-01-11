<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit;

use Engelsystem\Application;
use Engelsystem\Config\Config;
use Engelsystem\Container\Container;
use Engelsystem\Container\ServiceProvider;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use ReflectionClass;

#[CoversMethod(Application::class, '__construct')]
#[CoversMethod(Application::class, 'registerBaseBindings')]
#[CoversMethod(Application::class, 'path')]
#[CoversMethod(Application::class, 'registerPaths')]
#[CoversMethod(Application::class, 'setAppPath')]
#[CoversMethod(Application::class, 'register')]
#[CoversMethod(Application::class, 'bootstrap')]
#[CoversMethod(Application::class, 'getMiddleware')]
#[CoversMethod(Application::class, 'isBooted')]
class ApplicationTest extends TestCase
{
    public function testConstructor(): void
    {
        $app = new Application('.');

        $this->assertInstanceOf(Container::class, $app);
        $this->assertInstanceOf(ContainerInterface::class, $app);
        $this->assertSame($app, $app->get('app'));
        $this->assertSame($app, $app->get('container'));
        $this->assertSame($app, $app->get(Container::class));
        $this->assertSame($app, $app->get(Application::class));
        $this->assertSame($app, $app->get(ContainerInterface::class));
        $this->assertSame($app, Application::getInstance());
        $this->assertSame($app, Container::getInstance());
    }

    public function testAppPath(): void
    {
        $app = new Application();

        $this->assertFalse($app->has('path'));

        $app->setAppPath('.');
        $this->assertTrue($app->has('path'));
        $this->assertTrue($app->has('path.assets'));
        $this->assertTrue($app->has('path.config'));
        $this->assertTrue($app->has('path.lang'));
        $this->assertTrue($app->has('path.resources'));
        $this->assertTrue($app->has('path.resources.api'));
        $this->assertTrue($app->has('path.views'));
        $this->assertTrue($app->has('path.storage'));
        $this->assertTrue($app->has('path.cache'));
        $this->assertTrue($app->has('path.cache.routes'));
        $this->assertTrue($app->has('path.cache.views'));
        $this->assertTrue($app->has('path.public'));
        $this->assertTrue($app->has('path.assets.public'));

        $this->assertEquals(realpath('.'), $app->path());
        $this->assertEquals(realpath('.') . '/config', $app->get('path.config'));

        $app->setAppPath('./../');
        $this->assertEquals(realpath('../') . '/config', $app->get('path.config'));
    }

    public function testRegister(): void
    {
        $app = new Application();

        $serviceProvider = $this->mockServiceProvider($app, ['register']);
        $serviceProvider->expects($this->once())
            ->method('register');

        $app->register($serviceProvider);

        $anotherServiceProvider = $this->mockServiceProvider($app, ['register', 'boot']);
        $anotherServiceProvider->expects($this->once())
            ->method('register');
        $anotherServiceProvider->expects($this->once())
            ->method('boot');

        $app->bootstrap();
        $app->register($anotherServiceProvider);
    }

    public function testRegisterBoot(): void
    {
        $app = new Application();
        $app->bootstrap();

        $serviceProvider = $this->mockServiceProvider($app, ['register', 'boot']);
        $serviceProvider->expects($this->once())
            ->method('register');
        $serviceProvider->expects($this->once())
            ->method('boot');

        $app->register($serviceProvider);
    }

    public function testRegisterClassName(): void
    {
        $app = new Application();

        $serviceProvider = $this->mockServiceProvider($app, ['register']);

        $serviceProvider->expects($this->once())
            ->method('register');

        $app->instance(ServiceProvider::class, $serviceProvider);
        $app->register(ServiceProvider::class);
    }

    public function testBootstrap(): void
    {
        $app = $this->getMockBuilder(Application::class)
            ->onlyMethods(['register'])
            ->getMock();

        $serviceProvider = $this->mockServiceProvider($app, ['boot']);
        $serviceProvider->expects($this->once())
            ->method('boot');

        $app->expects($this->once())
            ->method('register')
            ->with($serviceProvider);

        $config = $this->getMockBuilder(Config::class)
            ->getMock();

        $middleware = [MiddlewareInterface::class];
        $matcher = $this->exactly(2);
        $config->expects($matcher)
            ->method('get')
            ->willReturnCallback(function (...$parameters) use ($middleware, $serviceProvider, $matcher) {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame('providers', $parameters[0]);
                    return [$serviceProvider];
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame('middleware', $parameters[0]);
                    return $middleware;
                }
            });

        $property = (new ReflectionClass($app))->getProperty('serviceProviders');
        $property->setValue($app, [$serviceProvider]);

        $app->bootstrap($config);

        $this->assertTrue($app->isBooted());
        $this->assertEquals($middleware, $app->getMiddleware());

        // Run bootstrap another time to ensure that providers are registered only once
        $app->bootstrap($config);
    }

    protected function mockServiceProvider(Application $app, array $methods = []): ServiceProvider&MockObject
    {
        return $this->getMockBuilder(ServiceProvider::class)
            ->setConstructorArgs([$app])
            ->onlyMethods($methods)
            ->getMock();
    }
}
