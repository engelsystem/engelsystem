<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers\Schedule;

use Engelsystem\Helpers\Schedule\ScheduleGenerator;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(ScheduleGenerator::class, '__construct')]
#[CoversMethod(ScheduleGenerator::class, 'getName')]
#[CoversMethod(ScheduleGenerator::class, 'getVersion')]
class ScheduleGeneratorTest extends TestCase
{
    public function testCreateDefaults(): void
    {
        $conferenceColor = new ScheduleGenerator();

        $this->assertNull($conferenceColor->getName());
        $this->assertNull($conferenceColor->getVersion());
    }

    public function testCreate(): void
    {
        $conferenceColor = new ScheduleGenerator('Engelsystem', '1.2.3');

        $this->assertEquals('Engelsystem', $conferenceColor->getName());
        $this->assertEquals('1.2.3', $conferenceColor->getVersion());
    }
}
