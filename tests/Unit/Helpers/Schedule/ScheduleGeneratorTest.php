<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers\Schedule;

use Engelsystem\Helpers\Schedule\ScheduleGenerator;
use Engelsystem\Test\Unit\TestCase;

class ScheduleGeneratorTest extends TestCase
{
    /**
     * @covers \Engelsystem\Helpers\Schedule\ScheduleGenerator::__construct
     * @covers \Engelsystem\Helpers\Schedule\ScheduleGenerator::getName
     * @covers \Engelsystem\Helpers\Schedule\ScheduleGenerator::getVersion
     */
    public function testCreateDefaults(): void
    {
        $conferenceColor = new ScheduleGenerator();

        $this->assertNull($conferenceColor->getName());
        $this->assertNull($conferenceColor->getVersion());
    }

    /**
     * @covers \Engelsystem\Helpers\Schedule\ScheduleGenerator::__construct
     * @covers \Engelsystem\Helpers\Schedule\ScheduleGenerator::getName
     * @covers \Engelsystem\Helpers\Schedule\ScheduleGenerator::getVersion
     */
    public function testCreate(): void
    {
        $conferenceColor = new ScheduleGenerator('Engelsystem', '1.2.3');

        $this->assertEquals('Engelsystem', $conferenceColor->getName());
        $this->assertEquals('1.2.3', $conferenceColor->getVersion());
    }
}
