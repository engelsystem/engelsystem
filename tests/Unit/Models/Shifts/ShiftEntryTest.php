<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Models\Shifts;

use Engelsystem\Models\AngelType;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\Models\ModelTest;

class ShiftEntryTest extends ModelTest
{
    /**
     * @covers \Engelsystem\Models\Shifts\ShiftEntry::shift
     * @covers \Engelsystem\Models\Shifts\ShiftEntry::angelType
     */
    public function testShift(): void
    {
        /** @var Shift $shift */
        $shift = Shift::factory()->create();
        /** @var AngelType $angelType */
        $angelType = AngelType::factory()->create();
        /** @var User $user */
        $user = User::factory()->create();

        $model = new ShiftEntry();
        $model->shift()->associate($shift);
        $model->angelType()->associate($angelType);
        $model->user()->associate($user);
        $model->save();

        $model = ShiftEntry::find(1);
        $this->assertEquals($shift->id, $model->shift->id);
        $this->assertEquals($angelType->id, $model->angelType->id);
        $this->assertEquals($user->id, $model->user->id);

        $this->assertArrayNotHasKey('freeloaded_comment', $model->toArray());
    }
}
