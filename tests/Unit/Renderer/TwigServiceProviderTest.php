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
use Engelsystem\Test\Unit\ServiceProviderTest;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass as Reflection;
use ReflectionException;
use Symfony\Component\VarDumper\VarDumper;
use Twig\Environment as Twig;
use Twig\Extension\AbstractExtension;
use Twig\Extension\CoreExtension as TwigCore;
use Twig\Extension\ExtensionInterface as ExtensionInterface;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface as TwigLoaderInterface;

class TwigServiceProviderTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Renderer\TwigServiceProvider::register
     * @covers \Engelsystem\Renderer\TwigServiceProvider::registerTwigExtensions
     */
    public function testRegister(): void
    {
        $app = $this->getApp(['alias', 'tag']);

        $className = 'Foo\Bar\Class';
        $classAlias = 'twig.extension.foo';

        $app->expects($this->once())
            ->method('alias')
            ->with($className, $classAlias);

        $app->expects($this->once())
            ->method('tag')
            ->with($classAlias, ['twig.extension']);

        /** @var TwigServiceProvider|MockObject $serviceProvider */
        $serviceProvider = $this->getMockBuilder(TwigServiceProvider::class)
            ->setConstructorArgs([$app])
            ->onlyMethods(['registerTwigEngine'])
            ->getMock();
        $serviceProvider->expects($this->once())
            ->method('registerTwigEngine');
        $this->setExtensionsTo($serviceProvider, ['foo' => 'Foo\Bar\Class']);

        $serviceProvider->register();
    }

    /**
     * @covers \Engelsystem\Renderer\TwigServiceProvider::boot
     */
    public function testBoot(): void
    {
        /** @var Twig|MockObject $twig */
        $twig = $this->createMock(Twig::class);
        /** @var Twig|MockObject $textTwig */
        $textTwig = $this->createMock(Twig::class);
        /** @var ExtensionInterface|MockObject $firsExtension */
        $firsExtension = $this->getMockForAbstractClass(ExtensionInterface::class);
        /** @var ExtensionInterface|MockObject $secondExtension */
        $secondExtension = $this->getMockForAbstractClass(ExtensionInterface::class);
        /** @var Develop|MockObject $devExtension */
        $devExtension = $this->createMock(Develop::class);
        /** @var VarDumper|MockObject $dumper */
        $dumper = $this->createMock(VarDumper::class);
        /** @var FilesystemLoader|MockObject $loader1 */
        $loader1 = $this->createMock(FilesystemLoader::class);
        $loader2 = (object) [];

        $this->app->instance('twig.environment', $twig);
        $this->app->instance('twig.textEnvironment', $textTwig);
        $this->app->instance('twig.extension.develop', $devExtension);
        $this->app->instance('a', $firsExtension);
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

        $twig->expects($this->exactly(2))
            ->method('addExtension')
            ->withConsecutive([$firsExtension], [$secondExtension]);

        $textTwig->expects($this->exactly(2))
            ->method('addExtension')
            ->withConsecutive([$firsExtension], [$secondExtension]);

        $devExtension->expects($this->once())
            ->method('setDumper')
            ->with($dumper);

        $serviceProvider = new TwigServiceProvider($this->app);
        $serviceProvider->boot();
    }

    /**
     * @covers \Engelsystem\Renderer\TwigServiceProvider::registerTwigEngine
     */
    public function testRegisterTwigEngine(): void
    {
        /** @var TwigEngine|MockObject $twigEngine */
        $twigEngine = $this->createMock(TwigEngine::class);
        /** @var TwigLoader|MockObject $twigLoader */
        $twigLoader = $this->createMock(TwigLoader::class);
        /** @var TwigTextLoader|MockObject $twigTextLoader */
        $twigTextLoader = $this->createMock(TwigTextLoader::class);
        /** @var Twig|MockObject $twig */
        $twig = $this->createMock(Twig::class);
        /** @var Config|MockObject $config */
        $config = $this->createMock(Config::class);
        /** @var TwigCore|MockObject $twigCore */
        $twigCore = $this->getMockForAbstractClass(
            AbstractExtension::class,
            [],
            '',
            true,
            true,
            true,
            ['setTimezone']
        );
        /** @var Twig|MockObject $twigText */
        $twigText = $this->createMock(Twig::class);
        /** @var TwigEngine|MockObject $twigTextEngine */
        $twigTextEngine = $this->createMock(TwigEngine::class);
        /** @var ExtendsTokenParser|MockObject $extendsTokenParser */
        $extendsTokenParser = $this->createMock(ExtendsTokenParser::class);

        $app = $this->getApp(['make', 'instance', 'tag', 'get']);

        $viewsPath = __DIR__ . '/Stub';

        $app->expects($this->exactly(7))
            ->method('make')
            ->withConsecutive(
                [TwigLoader::class, ['paths' => [$viewsPath, $viewsPath . '/..']]],
                [TwigTextLoader::class, ['paths' => [$viewsPath, $viewsPath . '/..']]],
                [Twig::class, ['options' => [
                    'cache'            => false,
                    'auto_reload'      => true,
                    'strict_variables' => true,
                    'debug'            => true,
                ]]],
                [TwigEngine::class],
                [Twig::class, ['loader' => $twigTextLoader, 'options' => [
                    'cache'            => false,
                    'auto_reload'      => true,
                    'strict_variables' => true,
                    'debug'            => true,
                    'autoescape'       => false,
                ]]],
                [TwigEngine::class, ['twig' => $twigText]],
                [ExtendsTokenParser::class],
            )->willReturnOnConsecutiveCalls(
                $twigLoader,
                $twigTextLoader,
                $twig,
                $twigEngine,
                $twigText,
                $twigTextEngine,
                $extendsTokenParser,
            );

        $app->expects($this->exactly(10))
            ->method('instance')
            ->withConsecutive(
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
            );

        $app->expects($this->exactly(5))
            ->method('get')
            ->withConsecutive(['path.views'], ['path.views'], ['config'], ['path.cache.views'], ['path.resources'])
            ->willReturnOnConsecutiveCalls($viewsPath, $viewsPath, $config, 'cache/views', '/resources');

        $app->expects($this->exactly(4))
            ->method('tag')
            ->withConsecutive(
                ['twig.loader', ['twig.loader']],
                ['twig.textLoader', ['twig.loader']],
                ['renderer.twigTextEngine', ['renderer.engine']], // Text goes first to catch .text.twig files
                ['renderer.twigEngine', ['renderer.engine']],
            );

        $config->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(['environment'], ['timezone'])
            ->willReturnOnConsecutiveCalls('development', 'The/World');

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
     * @throws ReflectionException
     */
    protected function setExtensionsTo(TwigServiceProvider $serviceProvider, array $extensions): void
    {
        $reflection = new Reflection(get_class($serviceProvider));

        $property = $reflection->getProperty('extensions');

        $property->setValue($serviceProvider, $extensions);
    }
}
