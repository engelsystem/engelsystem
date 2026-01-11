<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Models\Shifts;

use Engelsystem\Models\AngelType;
use Engelsystem\Models\Location;
use Engelsystem\Models\Shifts\NeededAngelType;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftType;
use Engelsystem\Test\Unit\Models\ModelTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(NeededAngelType::class, 'location')]
#[CoversMethod(NeededAngelType::class, 'shift')]
#[CoversMethod(NeededAngelType::class, 'shiftType')]
#[CoversMethod(NeededAngelType::class, 'angelType')]
class NeededAngelTypeTest extends ModelTestCase
{
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
