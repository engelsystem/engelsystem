<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit;

use Carbon\Carbon;
use Engelsystem\Models\Location;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftType;
use Engelsystem\ShiftCalendarRenderer;

class ShiftCalendarRendererTest extends TestCase
{
    /**
     * @covers \Engelsystem\ShiftCalendarRenderer::mergeTimeSpanIntoList
     */
    public function testMergeTimeSpanIntoListNewDate(): void
    {
        $times = [];
        $start = Carbon::create(2026, 1, 24, 10, 0);
        $end   = Carbon::create(2026, 1, 24, 12, 0);

        $result = ShiftCalendarRenderer::mergeTimeSpanIntoList($times, $start, $end);

        $this->assertEquals($result, ['2026-01-24' => [$start, $end]]);
    }

    /**
     * @covers \Engelsystem\ShiftCalendarRenderer::mergeTimeSpanIntoList
     */
    public function testMergeTimeSpanIntoListExtendEarlier(): void
    {
        $existingStart = Carbon::create(2026, 1, 24, 10, 0);
        $existingEnd   = Carbon::create(2026, 1, 24, 12, 0);
        $times = ['2026-01-24' => [$existingStart, $existingEnd]];

        $newStart = Carbon::create(2026, 1, 24, 8, 0);
        $newEnd   = Carbon::create(2026, 1, 24, 11, 0);
        $result   = ShiftCalendarRenderer::mergeTimeSpanIntoList($times, $newStart, $newEnd);

        $this->assertEquals($result, ['2026-01-24' => [$newStart, $existingEnd]]);
    }

    /**
     * @covers \Engelsystem\ShiftCalendarRenderer::mergeTimeSpanIntoList
     */
    public function testMergeTimeSpanIntoListExtendLater(): void
    {
        $existingStart = Carbon::create(2026, 1, 24, 10, 0);
        $existingEnd   = Carbon::create(2026, 1, 24, 12, 0);
        $times = ['2026-01-24' => [$existingStart, $existingEnd]];

        $newStart = Carbon::create(2026, 1, 24, 11, 0);
        $newEnd   = Carbon::create(2026, 1, 24, 14, 0);
        $result   = ShiftCalendarRenderer::mergeTimeSpanIntoList($times, $newStart, $newEnd);

        $this->assertEquals($result, ['2026-01-24' => [$existingStart, $newEnd]]);
    }

    /**
     * @covers \Engelsystem\ShiftCalendarRenderer::mergeTimeSpanIntoList
     */
    public function testMergeTimeSpanIntoListWithinExisting(): void
    {
        $existingStart = Carbon::create(2026, 1, 24, 10, 0);
        $existingEnd   = Carbon::create(2026, 1, 24, 14, 0);
        $times = ['2026-01-24' => [$existingStart, $existingEnd]];

        $newStart = Carbon::create(2026, 1, 24, 11, 0);
        $newEnd   = Carbon::create(2026, 1, 24, 12, 0);
        $result   = ShiftCalendarRenderer::mergeTimeSpanIntoList($times, $newStart, $newEnd);

        $this->assertEquals($result, ['2026-01-24' => [$existingStart, $existingEnd]]);
    }

    private function createShift(Carbon $start, Carbon $end): Shift
    {
        return Shift::factory()->create([
            'location_id' => Location::factory()->create()->id,
            'shift_type_id' => ShiftType::factory()->create()->id,
            'start' => $start,
            'end' => $end,
        ]);
    }

    /**
     * @covers \Engelsystem\ShiftCalendarRenderer::collectStartAndEndPerDay
     */
    public function testCollectStartAndEndPerDaySimpleShift(): void
    {
        $shiftStart = Carbon::create(2026, 1, 24, 10, 0);
        $shiftEnd   = Carbon::create(2026, 1, 24, 12, 0);

        $result = ShiftCalendarRenderer::collectStartAndEndPerDay([$this->createShift($shiftStart, $shiftEnd)]);

        $this->assertEquals(['2026-01-24' => [
            Carbon::create(2026, 1, 24, 9, 30), Carbon::create(2026, 1, 24, 12, 29),
        ]], $result);
    }

    /**
     * @covers \Engelsystem\ShiftCalendarRenderer::collectStartAndEndPerDay
     */
    public function testCollectStartAndEndPerDayMultipleShiftsSameDay(): void
    {
        $shift1Start = Carbon::create(2026, 1, 24, 10, 0);
        $shift1End   = Carbon::create(2026, 1, 24, 12, 0);
        $shift2Start = Carbon::create(2026, 1, 24, 14, 0);
        $shift2End   = Carbon::create(2026, 1, 24, 16, 0);

        $shift1 = $this->createShift($shift1Start, $shift1End);
        $shift2 = $this->createShift($shift2Start, $shift2End);

        $result = ShiftCalendarRenderer::collectStartAndEndPerDay([$shift1, $shift2]);

        $this->assertEquals(['2026-01-24' => [
                Carbon::create(2026, 1, 24, 9, 30),Carbon::create(2026, 1, 24, 16, 29),
            ]], $result);
    }

    /**
     * @covers \Engelsystem\ShiftCalendarRenderer::collectStartAndEndPerDay
     */
    public function testCollectStartAndEndPerDayOverMidnight(): void
    {
        $start = Carbon::create(2026, 1, 24, 22, 0);
        $end = Carbon::create(2026, 1, 25, 2, 0);

        $result = ShiftCalendarRenderer::collectStartAndEndPerDay([$this->createShift($start, $end)]);

        // Exactly two entries: start day and end day
        $this->assertEquals([
            '2026-01-24' => [Carbon::create(2026, 1, 24, 21, 30), Carbon::create(2026, 1, 24, 23, 59)],
            '2026-01-25' => [Carbon::create(2026, 1, 25, 0, 0), Carbon::create(2026, 1, 25, 2, 29)],
        ], $result);
    }

    /**
     * @covers \Engelsystem\ShiftCalendarRenderer::collectStartAndEndPerDay
     */
    public function testCollectStartAndEndPerDayEmptyShifts(): void
    {
        $result = ShiftCalendarRenderer::collectStartAndEndPerDay([]);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * @covers \Engelsystem\ShiftCalendarRenderer::collectStartAndEndPerDay
     */
    public function testCollectStartAndEndPerDayMultipleDays(): void
    {
        $shift1Start = Carbon::create(2026, 1, 24, 10, 0);
        $shift1End   = Carbon::create(2026, 1, 24, 12, 0);
        $shift2Start = Carbon::create(2026, 1, 25, 14, 0);
        $shift2End   = Carbon::create(2026, 1, 25, 16, 0);

        $result = ShiftCalendarRenderer::collectStartAndEndPerDay(
            [$this->createShift($shift1Start, $shift1End), $this->createShift($shift2Start, $shift2End)]
        );

        $this->assertEquals([
            '2026-01-24' => [Carbon::create(2026, 1, 24, 9, 30), Carbon::create(2026, 1, 24, 12, 29)],
            '2026-01-25' => [Carbon::create(2026, 1, 25, 13, 30), Carbon::create(2026, 1, 25, 16, 29)],
            ], $result);
    }
}
