<?php

namespace Engelsystem\Test\Unit\Exceptions;

use Engelsystem\Exceptions\Handler;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as Mock;

class HandlerTest extends TestCase
{
    /**
     * @covers \Engelsystem\Exceptions\Handler::__construct()
     * @covers \Engelsystem\Exceptions\Handler::register()
     */
    public function testRegister()
    {
        /** @var Handler|Mock $handler */
        $handler = $this->getMockForAbstractClass(Handler::class);
        $this->assertInstanceOf(Handler::class, $handler);
        $handler->register();
    }

    /**
     * @covers \Engelsystem\Exceptions\Handler::setEnvironment()
     * @covers \Engelsystem\Exceptions\Handler::getEnvironment()
     */
    public function testEnvironment()
    {
        /** @var Handler|Mock $handler */
        $handler = $this->getMockForAbstractClass(Handler::class);

        $handler->setEnvironment(Handler::ENV_DEVELOPMENT);
        $this->assertEquals(Handler::ENV_DEVELOPMENT, $handler->getEnvironment());

        $handler->setEnvironment(Handler::ENV_PRODUCTION);
        $this->assertEquals(Handler::ENV_PRODUCTION, $handler->getEnvironment());
    }
}
