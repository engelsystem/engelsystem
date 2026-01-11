<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Renderer\Twig\Extensions;

use Engelsystem\Renderer\Twig\Extensions\Csrf;
use PHPUnit\Framework\Attributes\CoversMethod;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

#[CoversMethod(Csrf::class, 'getFunctions')]
#[CoversMethod(Csrf::class, 'getCsrfField')]
#[CoversMethod(Csrf::class, '__construct')]
#[CoversMethod(Csrf::class, 'getCsrfToken')]
class CsrfTest extends ExtensionTestCase
{
    public function testGetGlobals(): void
    {
        $session = $this->createStub(SessionInterface::class);

        $extension = new Csrf($session);
        $functions = $extension->getFunctions();

        $this->assertExtensionExists('csrf', [$extension, 'getCsrfField'], $functions, ['is_safe' => ['html']]);
        $this->assertExtensionExists('csrf_token', [$extension, 'getCsrfToken'], $functions);
    }

    public function testGetCsrfField(): void
    {
        $extension = $this->getMockBuilder(Csrf::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCsrfToken'])
            ->getMock();

        $extension->expects($this->once())
            ->method('getCsrfToken')
            ->willReturn('SomeRandomCsrfToken');

        $this->assertEquals(
            '<input type="hidden" name="_token" value="SomeRandomCsrfToken">',
            $extension->getCsrfField()
        );
    }

    public function testGetCsrfToken(): void
    {
        $session = $this->createMock(SessionInterface::class);
        $session->expects($this->once())
            ->method('get')
            ->with('_token')
            ->willReturn('SomeOtherCsrfToken');

        $extension = new Csrf($session);
        $this->assertEquals('SomeOtherCsrfToken', $extension->getCsrfToken());
    }
}
