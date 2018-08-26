<?php

namespace Engelsystem\Test\Unit\Renderer;

use Engelsystem\Renderer\TwigEngine;
use Engelsystem\Renderer\TwigLoader;
use Engelsystem\Renderer\TwigServiceProvider;
use Engelsystem\Test\Unit\ServiceProviderTest;
use PHPUnit\Framework\MockObject\MockObject;
use Twig_Environment as Twig;
use Twig_LoaderInterface as TwigLoaderInterface;

class TwigServiceProviderTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Renderer\TwigServiceProvider::register
     * @covers \Engelsystem\Renderer\TwigServiceProvider::registerTwigEngine
     */
    public function testRegister()
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

        $app->expects($this->exactly(4))
            ->method('instance')
            ->withConsecutive(
                [TwigLoader::class, $twigLoader],
                [TwigLoaderInterface::class, $twigLoader],
                [Twig::class, $twig],
                ['renderer.twigEngine', $twigEngine]
            );

        $app->expects($this->once())
            ->method('get')
            ->with('path.views')
            ->willReturn($viewsPath);

        $this->setExpects($app, 'tag', ['renderer.twigEngine', ['renderer.engine']]);

        $serviceProvider = new TwigServiceProvider($app);
        $serviceProvider->register();
    }
}
