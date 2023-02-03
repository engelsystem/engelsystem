<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Renderer\Twig\Extensions;

use Engelsystem\Http\Request;
use Engelsystem\Renderer\Twig\Extensions\Legacy;
use PHPUnit\Framework\MockObject\MockObject;

class LegacyTest extends ExtensionTest
{
    /**
     * @covers \Engelsystem\Renderer\Twig\Extensions\Legacy::getFunctions
     */
    public function testGetFunctions(): void
    {
        $isSafeHtml = ['is_safe' => ['html']];
        /** @var Request|MockObject $request */
        $request = $this->createMock(Request::class);

        $extension = new Legacy($request);
        $functions = $extension->getFunctions();

        $this->assertExtensionExists('menu', 'make_navigation', $functions, $isSafeHtml);
        $this->assertExtensionExists('menuUserShiftState', 'User_shift_state_render', $functions, $isSafeHtml);
        $this->assertExtensionExists('menuUserHints', 'header_render_hints', $functions, $isSafeHtml);
        $this->assertExtensionExists('menuLanguages', 'make_language_select', $functions, $isSafeHtml);
        $this->assertExtensionExists('page', [$extension, 'getPage'], $functions);
        $this->assertExtensionExists('msg', 'msg', $functions, $isSafeHtml);
    }

    /**
     * @covers \Engelsystem\Renderer\Twig\Extensions\Legacy::__construct
     * @covers \Engelsystem\Renderer\Twig\Extensions\Legacy::getPage
     */
    public function testIsAuthenticated(): void
    {
        /** @var Request|MockObject $request */
        $request = $this->createMock(Request::class);

        $extension = new Legacy($request);

        $request->expects($this->exactly(2))
            ->method('has')
            ->with('p')
            ->willReturnOnConsecutiveCalls(true, false);

        $request->expects($this->once())
            ->method('get')
            ->with('p')
            ->willReturn('foo-bar');

        $request->expects($this->once())
            ->method('path')
            ->willReturn('batz');

        $this->assertEquals('foo-bar', $extension->getPage());
        $this->assertEquals('batz', $extension->getPage());
    }
}
