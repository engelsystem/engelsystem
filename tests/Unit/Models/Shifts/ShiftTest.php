<?php

namespace Engelsystem\Test\Unit\Models\Shifts;

use Engelsystem\Helpers\Carbon;
use Engelsystem\Models\Room;
use Engelsystem\Models\Shifts\Schedule;
use Engelsystem\Models\Shifts\ScheduleShift;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftType;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\Models\ModelTest;
use Illuminate\Database\Eloquent\Collection;

class ShiftTest extends ModelTest
{
    /**
     * @covers \Engelsystem\Models\Shifts\Shift::shiftType
     * @covers \Engelsystem\Models\Shifts\Shift::room
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
        /** @var Room $room */
        $room = Room::factory()->create();

        $model = new Shift([
            'title'          => 'Test shift',
            'description'    => 'Some description',
            'url'            => 'https://foo.bar/map',
            'start'          => Carbon::now(),
            'end'            => Carbon::now(),
            'shift_type_id'  => $shiftType->id,
            'room_id'        => $room->id,
            'transaction_id' => '',
            'created_by'     => $user1->id,
            'updated_by'     => $user2->id,
        ]);
        $model->save();

        $model = Shift::find(1);

        $this->assertEquals($shiftType->id, $model->shiftType->id);
        $this->assertEquals($room->id, $model->room->id);
        $this->assertEquals($user1->id, $model->createdBy->id);
        $this->assertEquals($user2->id, $model->updatedBy->id);
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
}
