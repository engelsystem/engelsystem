<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Renderer\Twig\Extensions;

use Engelsystem\Renderer\Twig\Extensions\Csrf;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CsrfTest extends ExtensionTest
{
    /**
     * @covers \Engelsystem\Renderer\Twig\Extensions\Csrf::getFunctions
     */
    public function testGetGlobals(): void
    {
        /** @var SessionInterface|MockObject $session */
        $session = $this->createMock(SessionInterface::class);

        $extension = new Csrf($session);
        $functions = $extension->getFunctions();

        $this->assertExtensionExists('csrf', [$extension, 'getCsrfField'], $functions, ['is_safe' => ['html']]);
        $this->assertExtensionExists('csrf_token', [$extension, 'getCsrfToken'], $functions);
    }

    /**
     * @covers \Engelsystem\Renderer\Twig\Extensions\Csrf::getCsrfField
     */
    public function testGetCsrfField(): void
    {
        /** @var Csrf|MockObject $extension */
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

    /**
     * @covers \Engelsystem\Renderer\Twig\Extensions\Csrf::__construct
     * @covers \Engelsystem\Renderer\Twig\Extensions\Csrf::getCsrfToken
     */
    public function testGetCsrfToken(): void
    {
        /** @var SessionInterface|MockObject $session */
        $session = $this->createMock(SessionInterface::class);
        $session->expects($this->once())
            ->method('get')
            ->with('_token')
            ->willReturn('SomeOtherCsrfToken');

        $extension = new Csrf($session);
        $this->assertEquals('SomeOtherCsrfToken', $extension->getCsrfToken());
    }
}
