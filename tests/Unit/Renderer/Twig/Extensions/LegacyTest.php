<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Renderer\Twig\Extensions;

use Engelsystem\Helpers\ShiftsRenderer;
use Engelsystem\Http\Request;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Renderer\Twig\Extensions\Legacy;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(Legacy::class, 'getFunctions')]
#[CoversMethod(Legacy::class, 'getFilters')]
#[CoversMethod(Legacy::class, 'renderShifts')]
#[CoversMethod(Legacy::class, '__construct')]
#[CoversMethod(Legacy::class, 'getPage')]
class LegacyTest extends ExtensionTestCase
{
    public function testGetFunctions(): void
    {
        $isSafeHtml = ['is_safe' => ['html']];
        $request = $this->createStub(Request::class);

        $extension = new Legacy($request);
        $functions = $extension->getFunctions();

        $this->assertExtensionExists('menu', 'make_navigation', $functions, $isSafeHtml);
        $this->assertExtensionExists('menuUserShiftState', 'User_shift_state_render', $functions, $isSafeHtml);
        $this->assertExtensionExists('menuUserHints', 'header_render_hints', $functions, $isSafeHtml);
        $this->assertExtensionExists('menuLanguages', 'make_language_select', $functions, $isSafeHtml);
        $this->assertExtensionExists('renderShifts', [$extension, 'renderShifts'], $functions, $isSafeHtml);
        $this->assertExtensionExists('page', [$extension, 'getPage'], $functions);
    }

    public function testGetFilters(): void
    {
        $request = $this->createStub(Request::class);

        $extension = new Legacy($request);
        $filters = $extension->getFilters();

        $this->assertFilterExists('dateWithEventDay', 'dateWithEventDay', $filters);
    }

    public function testRenderShifts(): void
    {
        $request = $this->createStub(Request::class);
        $renderingShifts = [new Shift()];
        $shiftsRenderer = $this->createMock(ShiftsRenderer::class);
        $shiftsRenderer->expects($this->once())
            ->method('render')
            ->willReturnCallback(function (array | Collection $shifts) use ($renderingShifts) {
                $this->assertEquals($renderingShifts, $shifts);
                return 'rendered shifts';
            });
        $this->app->instance(ShiftsRenderer::class, $shiftsRenderer);

        $extension = new Legacy($request);
        $output = $extension->renderShifts($renderingShifts);
        $this->assertEquals('rendered shifts', $output);
    }

    public function testGetPage(): void
    {
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
