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
     * @covers \Engelsystem\Models\Shifts\ShiftEntry::freeloadedBy
     */
    public function testShift(): void
    {
        /** @var Shift $shift */
        $shift = Shift::factory()->create();
        /** @var AngelType $angelType */
        $angelType = AngelType::factory()->create();
        /** @var User $user */
        $user = User::factory()->create();
        /** @var User $freeloadedBy */
        $freeloadedBy = User::factory()->create();

        $model = new ShiftEntry();
        $model->shift()->associate($shift);
        $model->angelType()->associate($angelType);
        $model->user()->associate($user);
        $model->freeloadedBy()->associate($freeloadedBy);
        $model->save();

        $model = ShiftEntry::find(1);
        $this->assertEquals($shift->id, $model->shift->id);
        $this->assertEquals($angelType->id, $model->angelType->id);
        $this->assertEquals($freeloadedBy->id, $model->freeloadedBy->id);
        $this->assertEquals($user->id, $model->user->id);

        $this->assertArrayNotHasKey('freeloaded_comment', $model->toArray());
    }

    /**
     * @covers \Engelsystem\Models\Shifts\ShiftEntry::supervisedBy
     */
    public function testSupervisedBy(): void
    {
        /** @var Shift $shift */
        $shift = Shift::factory()->create();
        /** @var AngelType $angelType */
        $angelType = AngelType::factory()->create();
        /** @var User $user */
        $user = User::factory()->create();
        /** @var User $supervisor */
        $supervisor = User::factory()->create();

        $model = new ShiftEntry();
        $model->shift()->associate($shift);
        $model->angelType()->associate($angelType);
        $model->user()->associate($user);
        $model->supervisedBy()->associate($supervisor);
        $model->save();

        $model = ShiftEntry::find($model->id);
        $this->assertEquals($supervisor->id, $model->supervisedBy->id);
    }

    /**
     * @covers \Engelsystem\Models\Shifts\ShiftEntry
     */
    public function testCountsTowardQuota(): void
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
        $model->counts_toward_quota = false;
        $model->save();

        $model = ShiftEntry::find($model->id);
        $this->assertFalse($model->counts_toward_quota);

        // Test default value
        $model2 = new ShiftEntry();
        $this->assertTrue($model2->counts_toward_quota);
    }
}
