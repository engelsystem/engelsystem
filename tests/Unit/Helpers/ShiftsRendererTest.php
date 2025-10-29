<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers;

use Engelsystem\Helpers\ShiftsRenderer;
use Engelsystem\Helpers\Uuid;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\Shifts\Schedule;
use Engelsystem\Models\Shifts\ScheduleShift;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PHPUnit\Framework\MockObject\MockObject;

class ShiftsRendererTest extends TestCase
{
    use HasDatabase;

    /**
     * @covers \Engelsystem\Helpers\ShiftsRenderer::render
     */
    public function testRender(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        /** @var AngelType $angelType */
        $angelType = AngelType::factory()->create();
        /** @var Schedule $scheduleLocation */
        $scheduleLocation = Schedule::factory()->create(['needed_from_shift_type' => false]);
        /** @var Schedule $scheduleType */
        $scheduleType = Schedule::factory()->create(['needed_from_shift_type' => true]);

        /** @var Shift $shiftNormal */
        $shiftNormal = Shift::factory()->create();
        $shiftNormal->scheduleShift()->delete();
        /** @var Shift $shiftScheduleLocation */
        $shiftScheduleLocation = Shift::factory()->create();
        /** @var Shift $shiftScheduleType */
        $shiftScheduleType = Shift::factory()->create();

        $scheduleShiftLocation = new ScheduleShift(['guid' => Str::uuid()]);
        $scheduleShiftLocation->schedule()->associate($scheduleLocation);
        $scheduleShiftLocation->shift()->associate($shiftScheduleLocation);
        $scheduleShiftLocation->save();
        $scheduleShiftType = new ScheduleShift(['guid' => Str::uuid()]);
        $scheduleShiftType->schedule()->associate($scheduleType);
        $scheduleShiftType->shift()->associate($shiftScheduleType);
        $scheduleShiftType->save();

        $shiftNormal->neededAngelTypes()->create(['angel_type_id' => $angelType->id, 'count' => 3]);

        ShiftEntry::factory()->create([
            'shift_id' => $shiftNormal,
            'angel_type_id' => $angelType,
            'user_id' => $user,
        ]);

        /** @var ShiftsRenderer|MockObject $renderer */
        $renderer = $this->getMockBuilder(ShiftsRenderer::class)
            ->onlyMethods(['renderShiftCalendar'])
            ->getMock();
        $renderer->expects($this->once())
            ->method('renderShiftCalendar')
            ->willReturnCallback(function (array | Collection $shifts, array $neededAngelTypes, array $shiftEntries) {
                $this->assertCount(3, $shifts);

                $angelType = $neededAngelTypes[1][0];
                $this->assertNotEmpty($angelType);
                $this->assertArrayHasKey('name', $angelType);
                $this->assertArrayHasKey('restricted', $angelType);
                $this->assertArrayHasKey('shift_self_signup', $angelType);

                $entry = $shiftEntries[1][0];
                $this->assertNotEmpty($entry);

                return 'rendered table';
            });

        $output = $renderer->render(Shift::all());
        $this->assertEquals('rendered table', $output);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->initDatabase();
        Str::createUuidsUsing(Uuid::class . '::uuid');
    }
}
