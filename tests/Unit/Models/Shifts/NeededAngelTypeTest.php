<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Models\Shifts;

use Engelsystem\Models\AngelType;
use Engelsystem\Models\Room;
use Engelsystem\Models\Shifts\NeededAngelType;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Test\Unit\Models\ModelTest;

class NeededAngelTypeTest extends ModelTest
{
    /**
     * @covers \Engelsystem\Models\Shifts\NeededAngelType::room
     * @covers \Engelsystem\Models\Shifts\NeededAngelType::shift
     * @covers \Engelsystem\Models\Shifts\NeededAngelType::angelType
     */
    public function testShift(): void
    {
        /** @var Room $room */
        $room = Room::factory()->create();
        /** @var Shift $shift */
        $shift = Shift::factory()->create();
        /** @var AngelType $angelType */
        $angelType = AngelType::factory()->create();

        $model = new NeededAngelType();
        $model->room()->associate($room);
        $model->shift()->associate($shift);
        $model->angelType()->associate($angelType);
        $model->count = 3;
        $model->save();

        $model = NeededAngelType::find(1);
        $this->assertEquals($room->id, $model->room->id);
        $this->assertEquals($shift->id, $model->shift->id);
        $this->assertEquals($angelType->id, $model->angelType->id);
        $this->assertEquals(3, $model->count);
    }
}
