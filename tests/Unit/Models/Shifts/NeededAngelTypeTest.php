<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Models\Shifts;

use Engelsystem\Models\AngelType;
use Engelsystem\Models\Location;
use Engelsystem\Models\Shifts\NeededAngelType;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftType;
use Engelsystem\Test\Unit\Models\ModelTest;

class NeededAngelTypeTest extends ModelTest
{
    /**
     * @covers \Engelsystem\Models\Shifts\NeededAngelType::location
     * @covers \Engelsystem\Models\Shifts\NeededAngelType::shift
     * @covers \Engelsystem\Models\Shifts\NeededAngelType::shiftType
     * @covers \Engelsystem\Models\Shifts\NeededAngelType::angelType
     */
    public function testShift(): void
    {
        /** @var Location $location */
        $location = Location::factory()->create();
        /** @var Shift $shift */
        $shift = Shift::factory()->create();
        /** @var ShiftType $shiftType */
        $shiftType = ShiftType::factory()->create();
        /** @var AngelType $angelType */
        $angelType = AngelType::factory()->create();

        $model = new NeededAngelType();
        $model->location()->associate($location);
        $model->shift()->associate($shift);
        $model->shiftType()->associate($shiftType);
        $model->angelType()->associate($angelType);
        $model->count = 3;
        $model->save();

        $model = NeededAngelType::find(1);
        $this->assertEquals($location->id, $model->location->id);
        $this->assertEquals($shift->id, $model->shift->id);
        $this->assertEquals($shiftType->id, $model->shiftType->id);
        $this->assertEquals($angelType->id, $model->angelType->id);
        $this->assertEquals(3, $model->count);
    }
}
