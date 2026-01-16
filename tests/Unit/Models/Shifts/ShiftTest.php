<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Models\Shifts;

use Engelsystem\Config\Config;
use Engelsystem\Helpers\Carbon;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\Location;
use Engelsystem\Models\Shifts\NeededAngelType;
use Engelsystem\Models\Shifts\Schedule;
use Engelsystem\Models\Shifts\ScheduleShift;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\Shifts\ShiftType;
use Engelsystem\Models\Tag;
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
     * @covers \Engelsystem\Models\Shifts\Shift::scheduleShift
     */
    public function testScheduleShift(): void
    {
        /** @var Schedule $schedule */
        $schedule = Schedule::factory()->create();
        /** @var Collection|Shift[] $shifts */
        $shifts = Shift::factory(4)->create();

        (new ScheduleShift(['shift_id' => $shifts[0]->id, 'schedule_id' => $schedule->id, 'guid' => 'd']))->save();
        (new ScheduleShift(['shift_id' => $shifts[1]->id, 'schedule_id' => $schedule->id, 'guid' => 'e']))->save();
        (new ScheduleShift(['shift_id' => $shifts[2]->id, 'schedule_id' => $schedule->id, 'guid' => 'f']))->save();

        $this->assertEquals('d', Shift::find(1)->scheduleShift->guid);
        $this->assertEquals('e', Shift::find(2)->scheduleShift->guid);
        $this->assertEquals('f', Shift::find(3)->scheduleShift->guid);
        $this->assertNull(Shift::find(4)->scheduleShift?->guid);
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
     * @covers \Engelsystem\Models\Shifts\Shift::scopeNeedsUsers
     */
    public function testScopeNeedsUsers(): void
    {
        /** @var AngelType $angelType */
        $angelType = AngelType::factory()->create();
        /** @var Shift $shift */
        $shift = Shift::factory()->create();
        Shift::factory()->create();

        $this->assertCount(2, Shift::all());
        $this->assertCount(0, Shift::scopes('needsUsers')->get());

        NeededAngelType::factory()->create(['angel_type_id' => $angelType->id, 'shift_id' => $shift->id]);

        $this->assertTrue(Shift::count() >= 2);
        $this->assertCount(1, Shift::scopes('needsUsers')->get());
    }

    /**
     * @covers \Engelsystem\Models\Shifts\Shift::scopeNeedsUsers
     */
    public function testScopeNeedsUsersFromSchedule(): void
    {
        /** @var Schedule $schedule1 */
        $schedule1 = Schedule::factory()->create(['needed_from_shift_type' => true]);
        /** @var Schedule $schedule2 */
        $schedule2 = Schedule::factory()->create(['needed_from_shift_type' => false]);
        $shiftType = $schedule1->shiftType;
        /** @var AngelType $angelType */
        $angelType = AngelType::factory()->create();
        /** @var Shift $shift1 Via schedule shift type */
        $shift1 = Shift::factory()->create(['shift_type_id' => $shiftType->id]);
        /** @var Shift $shift2 Via schedule location */
        $shift2 = Shift::factory()->create();
        /** @var Shift $shift3 Direct */
        $shift3 = Shift::factory()->create();
        /** @var Shift $shift4 Via schedule location, no needed angel types */
        $shift4 = Shift::factory()->create();
        /** @var Shift $shift5 Empty shift */
        $shift5 = Shift::factory()->create();
        $location = $shift2->location;

        ScheduleShift::factory()->create(['shift_id' => $shift1->id, 'schedule_id' => $schedule1->id]);
        ScheduleShift::factory()->create(['shift_id' => $shift2->id, 'schedule_id' => $schedule2->id]);
        ScheduleShift::factory()->create(['shift_id' => $shift4->id, 'schedule_id' => $schedule2->id]);

        NeededAngelType::factory()->create(['angel_type_id' => $angelType->id, 'shift_type_id' => $shiftType->id]);
        NeededAngelType::factory()->create(['angel_type_id' => $angelType->id, 'location_id' => $location->id]);
        NeededAngelType::factory()->create(['angel_type_id' => $angelType->id, 'shift_id' => $shift3->id]);

        $this->assertTrue(Shift::count() >= 5);

        $shifts = Shift::scopes('needsUsers')->get()->pluck('id');
        $this->assertContains($shift1->id, $shifts, 'Shift should be selected via schedule shift type');
        $this->assertContains($shift2->id, $shifts, 'Shift should be selected via schedule location selected');
        $this->assertContains($shift3->id, $shifts, 'Shift should be selected via direct requirement selected');
        $this->assertNotContains($shift4->id, $shifts, 'Empty schedule location shift selected');
        $this->assertNotContains($shift5->id, $shifts, 'Empty shift selected');
    }

    /**
     * @covers \Engelsystem\Models\Shifts\Shift::nextShift
     */
    public function testNextShift(): void
    {
        $location = Location::factory()->create();
        $shiftType = ShiftType::factory()->create();
        $shift = Shift::factory()->create([
            'location_id' => $location->id,
            'shift_type_id' => $shiftType->id,
            'title' => 'Rocket start',
            'start' => Carbon::now(),
        ]);
        $nextShift = Shift::factory()->create([
            'location_id' => $location->id,
            'shift_type_id' => $shiftType->id,
            'title' => 'Rocket start',
            'start' => Carbon::now()->addHour(),
        ]);
        $otherShift = Shift::factory()->create([
            'location_id' => $location->id,
            'shift_type_id' => $shiftType->id,
            'title' => 'Rocket starts',
            'start' => Carbon::now()->addHours(3),
        ]);

        $this->assertEquals($nextShift->id, $shift->nextShift()->id);
        $this->assertEquals($otherShift->id, $nextShift->nextShift()->id);
        $this->assertNull($otherShift->nextShift());
    }

    /**
     * @covers \Engelsystem\Models\Shifts\Shift::previousShift
     */
    public function testPreviousShift(): void
    {
        $location = Location::factory()->create();
        $shiftType = ShiftType::factory()->create();
        $shift = Shift::factory()->create([
            'location_id' => $location->id,
            'shift_type_id' => $shiftType->id,
            'title' => 'Rocket start',
            'end' => Carbon::now(),
        ]);
        $previousShift = Shift::factory()->create([
            'location_id' => $location->id,
            'shift_type_id' => $shiftType->id,
            'title' => 'Rocket start',
            'end' => Carbon::now()->subHour(),
        ]);
        $otherShift = Shift::factory()->create([
            'location_id' => $location->id,
            'shift_type_id' => $shiftType->id,
            'title' => 'Rocket starts',
            'end' => Carbon::now()->subHours(3),
        ]);

        $this->assertEquals($previousShift->id, $shift->previousShift()->id);
        $this->assertEquals($otherShift->id, $previousShift->previousShift()->id);
        $this->assertNull($otherShift->previousShift());
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

    /**
     * @covers \Engelsystem\Models\Shifts\Shift::tags
     */
    public function testTags(): void
    {
        $user = User::factory()->create();
        $location = Location::factory()->create();
        $shiftType = ShiftType::factory()->create();

        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();

        $model = new Shift([
            'title' => 'testing tags',
            'location_id' => $location->id,
            'shift_type_id' => $shiftType->id,
            'created_by' => $user->id,
            'start' => new Carbon('2042-01-01 09:00'),
            'end' => new Carbon('2042-01-01 13:00'),
        ]);
        $model->save();

        $model->tags()->attach($tag1);
        $model->tags()->attach($tag2);

        /** @var Shift $savedModel */
        $savedModel = Shift::all()->last();
        $this->assertEquals($tag1->name, $savedModel->tags[0]->name);
        $this->assertEquals($tag2->name, $savedModel->tags[1]->name);
    }

    /**
     * @covers \Engelsystem\Models\Shifts\Shift::getEffectiveWorkCategory
     */
    public function testGetEffectiveWorkCategoryDefault(): void
    {
        /** @var ShiftType $shiftType */
        $shiftType = ShiftType::factory()->create(['work_category' => 'A']);

        /** @var Shift $shift */
        $shift = Shift::factory()->create([
            'shift_type_id' => $shiftType->id,
            'work_category_override' => null,
        ]);

        // Should use ShiftType's work_category
        $this->assertEquals('A', $shift->getEffectiveWorkCategory());
    }

    /**
     * @covers \Engelsystem\Models\Shifts\Shift::getEffectiveWorkCategory
     */
    public function testGetEffectiveWorkCategoryFromShiftType(): void
    {
        /** @var ShiftType $shiftType */
        $shiftType = ShiftType::factory()->create(['work_category' => 'C']);

        /** @var Shift $shift */
        $shift = Shift::factory()->create([
            'shift_type_id' => $shiftType->id,
            'work_category_override' => null,
        ]);

        // Should inherit from ShiftType
        $this->assertEquals('C', $shift->getEffectiveWorkCategory());
    }

    /**
     * @covers \Engelsystem\Models\Shifts\Shift::getEffectiveWorkCategory
     */
    public function testGetEffectiveWorkCategoryWithOverride(): void
    {
        /** @var ShiftType $shiftType */
        $shiftType = ShiftType::factory()->create(['work_category' => 'C']);

        /** @var Shift $shift */
        $shift = Shift::factory()->create([
            'shift_type_id' => $shiftType->id,
            'work_category_override' => 'A',
        ]);

        // Override should take precedence over ShiftType
        $this->assertEquals('A', $shift->getEffectiveWorkCategory());
    }

    /**
     * @covers \Engelsystem\Models\Shifts\Shift::getAllowsAccompanyingChildren
     */
    public function testGetAllowsAccompanyingChildrenDefault(): void
    {
        /** @var ShiftType $shiftType */
        $shiftType = ShiftType::factory()->create(['allows_accompanying_children' => false]);

        /** @var Shift $shift */
        $shift = Shift::factory()->create([
            'shift_type_id' => $shiftType->id,
            'allows_accompanying_children_override' => null,
        ]);

        // Should use ShiftType's value (false)
        $this->assertFalse($shift->getAllowsAccompanyingChildren());
    }

    /**
     * @covers \Engelsystem\Models\Shifts\Shift::getAllowsAccompanyingChildren
     */
    public function testGetAllowsAccompanyingChildrenFromShiftType(): void
    {
        /** @var ShiftType $shiftType */
        $shiftType = ShiftType::factory()->create(['allows_accompanying_children' => true]);

        /** @var Shift $shift */
        $shift = Shift::factory()->create([
            'shift_type_id' => $shiftType->id,
            'allows_accompanying_children_override' => null,
        ]);

        // Should inherit from ShiftType
        $this->assertTrue($shift->getAllowsAccompanyingChildren());
    }

    /**
     * @covers \Engelsystem\Models\Shifts\Shift::getAllowsAccompanyingChildren
     */
    public function testGetAllowsAccompanyingChildrenOverrideTrue(): void
    {
        /** @var ShiftType $shiftType */
        $shiftType = ShiftType::factory()->create(['allows_accompanying_children' => false]);

        /** @var Shift $shift */
        $shift = Shift::factory()->create([
            'shift_type_id' => $shiftType->id,
            'allows_accompanying_children_override' => true,
        ]);

        // Override should take precedence (false -> true)
        $this->assertTrue($shift->getAllowsAccompanyingChildren());
    }

    /**
     * @covers \Engelsystem\Models\Shifts\Shift::getAllowsAccompanyingChildren
     */
    public function testGetAllowsAccompanyingChildrenOverrideFalse(): void
    {
        /** @var ShiftType $shiftType */
        $shiftType = ShiftType::factory()->create(['allows_accompanying_children' => true]);

        /** @var Shift $shift */
        $shift = Shift::factory()->create([
            'shift_type_id' => $shiftType->id,
            'allows_accompanying_children_override' => false,
        ]);

        // Override should take precedence (true -> false)
        $this->assertFalse($shift->getAllowsAccompanyingChildren());
    }
}
