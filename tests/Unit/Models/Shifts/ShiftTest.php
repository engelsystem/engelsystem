<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Models\Shifts;

use Engelsystem\Config\Config;
use Engelsystem\Helpers\Carbon;
use Engelsystem\Models\Location;
use Engelsystem\Models\Shifts\NeededAngelType;
use Engelsystem\Models\Shifts\Schedule;
use Engelsystem\Models\Shifts\ScheduleShift;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\Shifts\ShiftType;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\Models\ModelTest;
use Illuminate\Database\Eloquent\Collection;

class ShiftTest extends ModelTest
{
    /**
     * @covers \Engelsystem\Models\Shifts\Shift::shiftType
     * @covers \Engelsystem\Models\Shifts\Shift::location
     * @covers \Engelsystem\Models\Shifts\Shift::createdBy
     * @covers \Engelsystem\Models\Shifts\Shift::updatedBy
     */
    public function testShiftType(): void
    {
        /** @var User $user1 */
        $user1 = User::factory()->create();
        /** @var User $user2 */
        $user2 = User::factory()->create();
        /** @var ShiftType $shiftType */
        $shiftType = ShiftType::factory()->create();
        /** @var Location $location */
        $location = Location::factory()->create();

        $model = new Shift([
            'title'          => 'Test shift',
            'description'    => 'Some description',
            'url'            => 'https://foo.bar/map',
            'start'          => Carbon::now(),
            'end'            => Carbon::now(),
            'shift_type_id'  => $shiftType->id,
            'location_id'    => $location->id,
            'transaction_id' => '',
            'created_by'     => $user1->id,
            'updated_by'     => $user2->id,
        ]);
        $model->save();

        $model = Shift::find(1);

        $this->assertEquals($shiftType->id, $model->shiftType->id);
        $this->assertEquals($location->id, $model->location->id);
        $this->assertEquals($user1->id, $model->createdBy->id);
        $this->assertEquals($user2->id, $model->updatedBy->id);
    }

    /**
     * @covers \Engelsystem\Models\Shifts\Shift::neededAngelTypes
     */
    public function testNeededAngelTypes(): void
    {
        /** @var Collection|Shift[] $shifts */
        $shifts = Shift::factory(3)->create();

        $this->assertCount(0, Shift::find(1)->neededAngelTypes);

        (NeededAngelType::factory()->make(['shift_id' => $shifts[0]->id, 'location_id' => null]))->save();
        (NeededAngelType::factory()->make(['shift_id' => $shifts[0]->id, 'location_id' => null]))->save();
        (NeededAngelType::factory()->make(['shift_id' => $shifts[1]->id, 'location_id' => null]))->save();
        (NeededAngelType::factory()->make(['shift_id' => $shifts[2]->id, 'location_id' => null]))->save();

        $this->assertCount(2, Shift::find(1)->neededAngelTypes);
        $this->assertEquals(1, Shift::find(1)->neededAngelTypes[0]->id);
        $this->assertEquals(2, Shift::find(1)->neededAngelTypes[1]->id);
        $this->assertEquals(3, Shift::find(2)->neededAngelTypes->first()->id);
        $this->assertEquals(4, Shift::find(3)->neededAngelTypes->first()->id);
    }

    /**
     * @covers \Engelsystem\Models\Shifts\Shift::schedule
     */
    public function testSchedule(): void
    {
        /** @var Schedule $schedule */
        $schedule = Schedule::factory()->create();
        /** @var Collection|Shift[] $shifts */
        $shifts = Shift::factory(3)->create();

        (new ScheduleShift(['shift_id' => $shifts[0]->id, 'schedule_id' => $schedule->id, 'guid' => 'a']))->save();
        (new ScheduleShift(['shift_id' => $shifts[1]->id, 'schedule_id' => $schedule->id, 'guid' => 'b']))->save();
        (new ScheduleShift(['shift_id' => $shifts[2]->id, 'schedule_id' => $schedule->id, 'guid' => 'c']))->save();

        $this->assertEquals(1, Shift::find(1)->schedule->id);
        $this->assertEquals(1, Shift::find(2)->schedule->id);
        $this->assertEquals(1, Shift::find(3)->schedule->id);
    }

    /**
     * @covers \Engelsystem\Models\Shifts\Shift::shiftEntries
     */
    public function testShiftEntries(): void
    {
        /** @var Shift $shift */
        $shift = Shift::factory()->make();
        $shift->save();

        ShiftEntry::factory(5)->create(['shift_id' => $shift->id]);

        $this->assertCount(5, $shift->shiftEntries);
    }

    /**
     * @covers \Engelsystem\Models\Shifts\Shift::isNightShift
     */
    public function testIsNightShiftDisabled(): void
    {
        $config = new Config(['night_shifts' => [
            'enabled'    => false,
            'start'      => 2,
            'end'        => 8,
            'multiplier' => 2,
        ]]);
        $this->app->instance('config', $config);

        $shift = new Shift([
            'start' => new Carbon('2042-01-01 04:00'),
            'end' => new Carbon('2042-01-01 05:00'),
        ]);

        // At night but disabled
        $this->assertFalse($shift->isNightShift());
    }

    /**
     * @return array{0: string, 1: string, 2: boolean}[]
     */
    public function nightShiftData(): array
    {
        // $start, $end, $isNightShift
        return [
            // Is night shift
            ['2042-01-01 04:00', '2042-01-01 05:00', true],
            // Is night shift
            ['2042-01-01 02:00', '2042-01-01 02:15', true],
            // Is night shift
            ['2042-01-01 07:45', '2042-01-01 08:00', true],
            // Starts as night shift
            ['2042-01-01 07:59', '2042-01-01 09:00', true],
            // Ends as night shift
            ['2042-01-01 00:00', '2042-01-01 02:01', true],
            // Equals night shift
            ['2042-01-01 02:00', '2042-01-01 08:00', true],
            // Contains night shift
            ['2042-01-01 01:00', '2042-01-01 09:00', true],
            // Too early
            ['2042-01-01 00:00', '2042-01-01 02:00', false],
            // Too late
            ['2042-01-01 08:00', '2042-01-01 10:00', false],
            // Out of range
            ['2042-01-01 23:00', '2042-01-02 01:00', false],
        ];
    }

    /**
     * @covers       \Engelsystem\Models\Shifts\Shift::isNightShift
     * @dataProvider nightShiftData
     */
    public function testIsNightShiftEnabled(string $start, string $end, bool $isNightShift): void
    {
        $config = new Config(['night_shifts' => [
            'enabled'    => true,
            'start'      => 2,
            'end'        => 8,
            'multiplier' => 2,
        ]]);
        $this->app->instance('config', $config);

        $shift = new Shift([
            'start' => new Carbon($start),
            'end' => new Carbon($end),
        ]);

        $this->assertEquals($isNightShift, $shift->isNightShift());
    }

    /**
     * @covers \Engelsystem\Models\Shifts\Shift::getNightShiftMultiplier
     */
    public function testGetNightShiftMultiplier(): void
    {
        $config = new Config(['night_shifts' => [
            'enabled'    => true,
            'start'      => 2,
            'end'        => 8,
            'multiplier' => 2,
        ]]);
        $this->app->instance('config', $config);

        $shift = new Shift([
            'start' => new Carbon('2042-01-01 02:00'),
            'end' => new Carbon('2042-01-01 04:00'),
        ]);

        $this->assertEquals(2, $shift->getNightShiftMultiplier());

        $config->set('night_shifts', array_merge($config->get('night_shifts'), ['enabled' => false]));
        $this->assertEquals(1, $shift->getNightShiftMultiplier());
    }
}
