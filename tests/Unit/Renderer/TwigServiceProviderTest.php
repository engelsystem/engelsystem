<?php

namespace Engelsystem\Test\Unit\Renderer;

use Engelsystem\Renderer\TwigEngine;
use Engelsystem\Renderer\TwigLoader;
use Engelsystem\Renderer\TwigServiceProvider;
use Engelsystem\Test\Unit\ServiceProviderTest;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass as Reflection;
use stdClass;
use Twig_Environment as Twig;
use Twig_ExtensionInterface as ExtensionInterface;
use Twig_LoaderInterface as TwigLoaderInterface;

class TwigServiceProviderTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Renderer\TwigServiceProvider::register
     * @covers \Engelsystem\Renderer\TwigServiceProvider::registerTwigExtensions
     */
    public function testRegister()
    {
        $app = $this->getApp(['make', 'instance', 'tag']);
        $class = $this->createMock(stdClass::class);

        $className = 'Foo\Bar\Class';
        $classAlias = 'twig.extension.foo';

        $app->expects($this->once())
            ->method('make')
            ->with('Foo\Bar\Class')
            ->willReturn($class);

        $app->expects($this->exactly(2))
            ->method('instance')
            ->withConsecutive(
                [$className, $class],
                [$classAlias, $class]
            );

        $app->expects($this->once())
            ->method('tag')
            ->with($classAlias, ['twig.extension']);

        /** @var TwigServiceProvider|MockObject $serviceProvider */
        $serviceProvider = $this->getMockBuilder(TwigServiceProvider::class)
            ->setConstructorArgs([$app])
            ->setMethods(['registerTwigEngine'])
            ->getMock();
        $serviceProvider->expects($this->once())
            ->method('registerTwigEngine');
        $this->setExtensionsTo($serviceProvider, ['foo' => 'Foo\Bar\Class']);

        $serviceProvider->register();
    }

    /**
     * @covers \Engelsystem\Renderer\TwigServiceProvider::boot
     */
    public function testBoot()
    {
        /** @var Twig|MockObject $twig */
        $twig = $this->createMock(Twig::class);
        /** @var ExtensionInterface|MockObject $firsExtension */
        $firsExtension = $this->getMockForAbstractClass(ExtensionInterface::class);
        /** @var ExtensionInterface|MockObject $secondExtension */
        $secondExtension = $this->getMockForAbstractClass(ExtensionInterface::class);

        $app = $this->getApp(['get', 'tagged']);

        $app->expects($this->once())
            ->method('get')
            ->with('twig.environment')
            ->willReturn($twig);
        $app->expects($this->once())
            ->method('tagged')
            ->with('twig.extension')
            ->willReturn([$firsExtension, $secondExtension]);

        $twig->expects($this->exactly(2))
            ->method('addExtension')
            ->withConsecutive($firsExtension, $secondExtension);

        $serviceProvider = new TwigServiceProvider($app);
        $serviceProvider->boot();
    }

    /**
     * @covers \Engelsystem\Renderer\TwigServiceProvider::registerTwigEngine
     */
    public function testRegisterTWigEngine()
    {
        /** @var TwigEngine|MockObject $htmlEngine */
        $twigEngine = $this->createMock(TwigEngine::class);
        /** @var TwigLoader|MockObject $twigLoader */
        $twigLoader = $this->createMock(TwigLoader::class);
        /** @var Twig|MockObject $twig */
        $twig = $this->createMock(Twig::class);

        $app = $this->getApp(['make', 'instance', 'tag', 'get']);

        $viewsPath = __DIR__ . '/Stub';

        $app->expects($this->exactly(3))
            ->method('make')
            ->withConsecutive(
                [TwigLoader::class, ['paths' => $viewsPath]],
                [Twig::class],
                [TwigEngine::class]
            )->willReturnOnConsecutiveCalls(
                $twigLoader,
                $twig,
                $twigEngine
            );

        $app->expects($this->exactly(6))
            ->method('instance')
            ->withConsecutive(
                [TwigLoader::class, $twigLoader],
                [TwigLoaderInterface::class, $twigLoader],
                ['twig.loader', $twigLoader],
                [Twig::class, $twig],
                ['twig.environment', $twig],
                ['renderer.twigEngine', $twigEngine]
            );

        $app->expects($this->once())
            ->method('get')
            ->with('path.views')
            ->willReturn($viewsPath);

        $this->setExpects($app, 'tag', ['renderer.twigEngine', ['renderer.engine']]);

        $serviceProvider = new TwigServiceProvider($app);
        $this->setExtensionsTo($serviceProvider, []);

        $serviceProvider->register();
    }

    /**
     * @param TwigServiceProvider $serviceProvider
     * @param array               $extensions
     * @throws \ReflectionException
     */
    protected function setExtensionsTo($serviceProvider, $extensions)
    {
        $reflection = new Reflection(get_class($serviceProvider));

        $property = $reflection->getProperty('extensions');
        $property->setAccessible(true);

        $property->setValue($serviceProvider, $extensions);
    }
}
