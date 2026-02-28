<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Renderer;

use Engelsystem\Config\Config;
use Engelsystem\Renderer\ExtendsTokenParser;
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
use Symfony\Component\VarDumper\VarDumper;
use Twig\Environment as Twig;
use Twig\Extension\CoreExtension as TwigCore;
use Twig\Extension\ExtensionInterface as ExtensionInterface;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface as TwigLoaderInterface;

#[CoversMethod(TwigServiceProvider::class, 'register')]
#[CoversMethod(TwigServiceProvider::class, 'registerTwigExtensions')]
#[CoversMethod(TwigServiceProvider::class, 'boot')]
#[CoversMethod(TwigServiceProvider::class, 'registerTwigEngine')]
class TwigServiceProviderTest extends ServiceProviderTestCase
{
    public function testRegister(): void
    {
        $app = $this->getAppMock(['alias', 'tag']);

        $className = 'Foo\Bar\Class';
        $classAlias = 'twig.extension.foo';

        $app->expects($this->once())
            ->method('alias')
            ->with($className, $classAlias);

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
        $loader1 = $this->createMock(FilesystemLoader::class);
        $loader2 = (object) [];

        $this->app->instance('twig.environment', $twig);
        $this->app->instance('twig.textEnvironment', $textTwig);
        $this->app->instance('twig.extension.develop', $devExtension);
        $this->app->instance('a', $firstExtension);
        $this->app->instance('b', $secondExtension);
        $this->app->tag(['a', 'b'], 'twig.extension');
        $this->app->instance('no-dir', '/this-dir-should-not-exist');
        $this->app->instance('other-dir', __DIR__ . '/Stub/');
        $this->app->tag(['no-dir', 'other-dir'], 'plugin.path');
        $this->app->singleton(VarDumper::class, fn () => $dumper);
        $this->app->instance('l1', $loader1);
        $this->app->instance('l2', $loader2);
        $this->app->tag(['l1', 'l2'], 'twig.loader');

        $this->setExpects($loader1, 'prependPath', [__DIR__ . '/Stub/views/']);

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

        $serviceProvider = new TwigServiceProvider($this->app);
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
        $extendsTokenParser = $this->createStub(ExtendsTokenParser::class);

        $app = $this->getAppMock(['make', 'instance', 'tag', 'get']);

        $viewsPath = __DIR__ . '/Stub';

        $matcher = $this->exactly(7);
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
                $twigText,
                $extendsTokenParser
            ) {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame(TwigLoader::class, $parameters[0]);
                    $this->assertSame(['paths' => [$viewsPath, $viewsPath . '/..']], $parameters[1]);
                    return $twigLoader;
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame(TwigTextLoader::class, $parameters[0]);
                    $this->assertSame(['paths' => [$viewsPath, $viewsPath . '/..']], $parameters[1]);
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
                if ($matcher->numberOfInvocations() === 7) {
                    $this->assertSame(ExtendsTokenParser::class, $parameters[0]);
                    $this->assertSame(['basePath' => '/resources'], $parameters[1]);
                    return $extendsTokenParser;
                }
            });

        $app->method('instance')
            ->willReturnMap([
                [TwigLoader::class, $twigLoader],
                [FilesystemLoader::class, $twigLoader],
                [TwigLoaderInterface::class, $twigLoader],
                ['twig.loader', $twigLoader],
                ['twig.textLoader', $twigTextLoader],
                [Twig::class, $twig],
                ['twig.environment', $twig],
                ['renderer.twigEngine', $twigEngine],
                ['twig.textEnvironment', $twigText],
                ['renderer.twigTextEngine', $twigTextEngine],
            ]);

        $app->method('get')
            ->willReturnMap([
                ['path.views', $viewsPath],
                ['path.views', $viewsPath],
                ['config', $config],
                ['path.cache.views', 'cache/views'],
                ['path.resources', '/resources'],
            ]);

        $app->method('tag')
            ->willReturnMap([
                ['twig.loader', ['twig.loader']],
                ['twig.textLoader', ['twig.loader']],
                ['renderer.twigTextEngine', ['renderer.engine']], // Text goes first to catch .text.twig files
                ['renderer.twigEngine', ['renderer.engine']],
            ]);

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

        $twig->expects($this->once())
            ->method('addTokenParser')
            ->with($extendsTokenParser);

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
