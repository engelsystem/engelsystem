<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Renderer;

use Engelsystem\Config\Config;
use Engelsystem\Renderer\Twig\Extensions\Develop;
use Engelsystem\Renderer\TwigEngine;
use Engelsystem\Renderer\TwigLoader;
use Engelsystem\Renderer\TwigServiceProvider;
use Engelsystem\Renderer\TwigTextLoader;
use Engelsystem\Test\Unit\Renderer\Stub\AbstractExtensionWithSetTimezone;
use Engelsystem\Test\Unit\ServiceProviderTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;
use ReflectionClass as Reflection;
use ReflectionException;
use stdClass;
use Symfony\Component\VarDumper\VarDumper;
use Twig\Environment as Twig;
use Twig\Extension\CoreExtension as TwigCore;
use Twig\Extension\ExtensionInterface as ExtensionInterface;
use Twig\Loader\LoaderInterface as TwigLoaderInterface;

#[CoversMethod(TwigServiceProvider::class, 'register')]
#[CoversMethod(TwigServiceProvider::class, 'registerTwigExtensions')]
#[CoversMethod(TwigServiceProvider::class, 'boot')]
#[CoversMethod(TwigServiceProvider::class, 'registerTwigEngine')]
class TwigServiceProviderTest extends ServiceProviderTestCase
{
    public function testRegister(): void
    {
        $app = $this->getAppMock(['make', 'instance', 'tag']);
        $class = $this->createStub(stdClass::class);

        $className = 'Foo\Bar\Class';
        $classAlias = 'twig.extension.foo';

        $app->expects($this->once())
            ->method('make')
            ->with('Foo\Bar\Class')
            ->willReturn($class);

        $matcher = $this->exactly(2);
        $app->expects($matcher)
            ->method('instance')
            ->willReturnCallback(function (...$parameters) use ($matcher, $className, $class, $classAlias): void {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame($className, $parameters[0]);
                    $this->assertSame($class, $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame($classAlias, $parameters[0]);
                    $this->assertSame($class, $parameters[1]);
                }
            });

        $app->expects($this->once())
            ->method('tag')
            ->with($classAlias, ['twig.extension']);

        $serviceProvider = $this->getMockBuilder(TwigServiceProvider::class)
            ->setConstructorArgs([$app])
            ->onlyMethods(['registerTwigEngine'])
            ->getMock();
        $serviceProvider->expects($this->once())
            ->method('registerTwigEngine');
        $this->setExtensionsTo($serviceProvider, ['foo' => 'Foo\Bar\Class']);

        $serviceProvider->register();
    }

    public function testBoot(): void
    {
        $twig = $this->createMock(Twig::class);
        $textTwig = $this->createMock(Twig::class);
        $firstExtension = $this->getStubBuilder(ExtensionInterface::class)->getStub();
        $secondExtension = $this->getStubBuilder(ExtensionInterface::class)->getStub();
        $devExtension = $this->createMock(Develop::class);
        $dumper = $this->createStub(VarDumper::class);

        $app = $this->getAppMock(['get', 'tagged', 'make']);

        $app->method('get')
            ->willReturnMap([
                ['twig.environment', $twig],
                ['twig.textEnvironment', $textTwig],
                ['twig.extension.develop', $devExtension],
            ]);
        $app->expects($this->once())
            ->method('tagged')
            ->with('twig.extension')
            ->willReturn([$firstExtension, $secondExtension]);
        $app->expects($this->once())
            ->method('make')
            ->with(VarDumper::class)
            ->willReturn($dumper);

        $matcher = $this->exactly(2);
        $twig->expects($matcher)
            ->method('addExtension')
            ->willReturnCallback(function (...$parameters) use ($matcher, $firstExtension, $secondExtension): void {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame($firstExtension, $parameters[0]);
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame($secondExtension, $parameters[0]);
                }
            });

        $matcher = $this->exactly(2);
        $textTwig->expects($matcher)
            ->method('addExtension')
            ->willReturnCallback(function (...$parameters) use ($matcher, $firstExtension, $secondExtension): void {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame($firstExtension, $parameters[0]);
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame($secondExtension, $parameters[0]);
                }
            });

        $devExtension->expects($this->once())
            ->method('setDumper')
            ->with($dumper);

        $serviceProvider = new TwigServiceProvider($app);
        $serviceProvider->boot();
    }

    public function testRegisterTwigEngine(): void
    {
        $twigEngine = $this->createStub(TwigEngine::class);
        $twigLoader = $this->createStub(TwigLoader::class);
        $twigTextLoader = $this->createStub(TwigTextLoader::class);
        $twig = $this->createMock(Twig::class);
        $config = $this->createStub(Config::class);
        $twigCore = $this->createMock(
            AbstractExtensionWithSetTimezone::class,
        );
        $twigText = $this->createStub(Twig::class);
        $twigTextEngine = $this->createStub(TwigEngine::class);

        $app = $this->getAppMock(['make', 'instance', 'tag', 'get']);

        $viewsPath = __DIR__ . '/Stub';

        $matcher = $this->exactly(6);
        $app->expects($matcher)
            ->method('make')
            ->willReturnCallback(function (...$parameters) use (
                $twigTextEngine,
                $twigEngine,
                $twig,
                $twigLoader,
                $matcher,
                $viewsPath,
                $twigTextLoader,
                $twigText
            ) {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame(TwigLoader::class, $parameters[0]);
                    $this->assertSame(['paths' => $viewsPath], $parameters[1]);
                    return $twigLoader;
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame(TwigTextLoader::class, $parameters[0]);
                    $this->assertSame(['paths' => $viewsPath], $parameters[1]);
                    return $twigTextLoader;
                }
                if ($matcher->numberOfInvocations() === 3) {
                    $this->assertSame(Twig::class, $parameters[0]);
                    $this->assertEquals(['options' => [
                    'cache'            => false,
                    'auto_reload'      => true,
                    'strict_variables' => true,
                    'debug'            => true,
                    ]], $parameters[1]);
                    return $twig;
                }
                if ($matcher->numberOfInvocations() === 4) {
                    $this->assertSame(TwigEngine::class, $parameters[0]);
                    return $twigEngine;
                }
                if ($matcher->numberOfInvocations() === 5) {
                    $this->assertSame(Twig::class, $parameters[0]);
                    $this->assertEquals(['loader' => $twigTextLoader, 'options' => [
                    'cache'            => false,
                    'auto_reload'      => true,
                    'strict_variables' => true,
                    'debug'            => true,
                    'autoescape'       => false,
                    ]], $parameters[1]);
                    return $twigText;
                }
                if ($matcher->numberOfInvocations() === 6) {
                    $this->assertSame(TwigEngine::class, $parameters[0]);
                    $this->assertSame(['twig' => $twigText], $parameters[1]);
                    return $twigTextEngine;
                }
            });

        $matcher = $this->exactly(9);
        $app->expects($matcher)
            ->method('instance')
            ->willReturnCallback(function (...$parameters) use (
                $matcher,
                $twigLoader,
                $twigTextLoader,
                $twig,
                $twigEngine,
                $twigText,
                $twigTextEngine
            ): void {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame(TwigLoader::class, $parameters[0]);
                    $this->assertSame($twigLoader, $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame(TwigLoaderInterface::class, $parameters[0]);
                    $this->assertSame($twigLoader, $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 3) {
                    $this->assertSame('twig.loader', $parameters[0]);
                    $this->assertSame($twigLoader, $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 4) {
                    $this->assertSame('twig.textLoader', $parameters[0]);
                    $this->assertSame($twigTextLoader, $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 5) {
                    $this->assertSame(Twig::class, $parameters[0]);
                    $this->assertSame($twig, $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 6) {
                    $this->assertSame('twig.environment', $parameters[0]);
                    $this->assertSame($twig, $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 7) {
                    $this->assertSame('renderer.twigEngine', $parameters[0]);
                    $this->assertSame($twigEngine, $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 8) {
                    $this->assertSame('twig.textEnvironment', $parameters[0]);
                    $this->assertSame($twigText, $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 9) {
                    $this->assertSame('renderer.twigTextEngine', $parameters[0]);
                    $this->assertSame($twigTextEngine, $parameters[1]);
                }
            });

        $app->method('get')
            ->willReturnMap([
                ['path.views', $viewsPath],
                ['config', $config],
                ['path.cache.views', 'cache/views'],
            ]);

        $matcher = $this->exactly(2);
        $app->expects($matcher)
            ->method('tag')->willReturnCallback(function (...$parameters) use ($matcher): void {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame('renderer.twigTextEngine', $parameters[0]);
                    $this->assertSame(['renderer.engine'], $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame('renderer.twigEngine', $parameters[0]);
                    $this->assertSame(['renderer.engine'], $parameters[1]);
                }
            });

        $config
            ->method('get')
            ->willReturnMap([
                ['environment', 'development'],
                ['timezone', 'The/World'],
            ]);

        $twig->expects($this->once())
            ->method('getExtension')
            ->with(TwigCore::class)
            ->willReturn($twigCore);

        $twigCore->expects($this->once())
            ->method('setTimezone')
            ->with('The/World');

        $serviceProvider = new TwigServiceProvider($app);
        $this->setExtensionsTo($serviceProvider, []);

        $serviceProvider->register();
    }

    /**
     * @param array<string, string> $extensions
     * @throws ReflectionException
     */
    protected function setExtensionsTo(TwigServiceProvider $serviceProvider, array $extensions): void
    {
        $reflection = new Reflection(get_class($serviceProvider));

        $property = $reflection->getProperty('extensions');

        $property->setValue($serviceProvider, $extensions);
    }
}
