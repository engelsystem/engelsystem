<?php

namespace Engelsystem\Test\Unit\Renderer\Twig\Extensions;

use Carbon\Carbon;
use Engelsystem\Renderer\Twig\Extensions\Globals;
use PHPUnit\Framework\MockObject\MockObject;

class GlobalsTest extends ExtensionTest
{
    /**
     * @covers \Engelsystem\Renderer\Twig\Extensions\Globals::getGlobals
     * @covers \Engelsystem\Renderer\Twig\Extensions\Globals::filterEventConfig
     */
    public function testGetGlobals()
    {
        global $user;
        $user = [];

        /** @var Globals|MockObject $extension */
        $extension = $this->getMockBuilder(Globals::class)
            ->setMethods(['getEventConfig'])
            ->getMock();

        $extension->expects($this->exactly(2))
            ->method('getEventConfig')
            ->willReturnOnConsecutiveCalls(
                null,
                [
                    'lorem'          => 'ipsum',
                    'event_end_date' => 1234567890,
                ]
            );

        $globals = $extension->getGlobals();

        $this->assertGlobalsExists('user', [], $globals);
        $this->assertGlobalsExists('event_config', [], $globals);

        $user['foo'] = 'bar';

        $globals = $extension->getGlobals();
        $this->assertGlobalsExists('user', ['foo' => 'bar'], $globals);
        $this->assertGlobalsExists('event_config', ['lorem' => 'ipsum'], $globals);

        $config = $globals['event_config'];
        $this->assertArrayHasKey('event_end_date', $config);
        /** @var Carbon $eventEndDate */
        $eventEndDate = $config['event_end_date'];
        $this->assertInstanceOf(Carbon::class, $eventEndDate);

        $eventEndDate->setTimezone('UTC');
        $this->assertEquals('2009-02-13 23:31:30', $eventEndDate->format('Y-m-d H:i:s'));
    }
}
