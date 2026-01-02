<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Models;

use Engelsystem\Models\User\User;
use Engelsystem\Models\UserSupervisorStatus;

class UserSupervisorStatusTest extends ModelTest
{
    /**
     * @covers \Engelsystem\Models\UserSupervisorStatus::user
     */
    public function testUser(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $model = new UserSupervisorStatus();
        $model->user()->associate($user);
        $model->save();

        $model = UserSupervisorStatus::find(1);
        $this->assertEquals($user->id, $model->user->id);
    }

    /**
     * @covers \Engelsystem\Models\UserSupervisorStatus::scopeWilling
     */
    public function testScopeWilling(): void
    {
        User::factory(3)->create()->each(function (User $user): void {
            UserSupervisorStatus::create([
                'user_id'              => $user->id,
                'willing_to_supervise' => true,
            ]);
        });

        User::factory(2)->create()->each(function (User $user): void {
            UserSupervisorStatus::create([
                'user_id'              => $user->id,
                'willing_to_supervise' => false,
            ]);
        });

        $willing = UserSupervisorStatus::willing()->get();
        $this->assertCount(3, $willing);
    }

    /**
     * @covers \Engelsystem\Models\UserSupervisorStatus::scopeTrained
     */
    public function testScopeTrained(): void
    {
        User::factory(2)->create()->each(function (User $user): void {
            UserSupervisorStatus::create([
                'user_id'                        => $user->id,
                'supervision_training_completed' => true,
            ]);
        });

        User::factory(3)->create()->each(function (User $user): void {
            UserSupervisorStatus::create([
                'user_id'                        => $user->id,
                'supervision_training_completed' => false,
            ]);
        });

        $trained = UserSupervisorStatus::trained()->get();
        $this->assertCount(2, $trained);
    }

    /**
     * @return array<string, array{bool, bool, bool, bool}>
     */
    public function canSuperviseDataProvider(): array
    {
        // [expected, willing, trained, requireTraining]
        return [
            'not willing, no training required'     => [false, false, false, false],
            'not willing, training required'        => [false, false, false, true],
            'not willing but trained'               => [false, false, true, false],
            'willing, no training required'         => [true, true, false, false],
            'willing, training required but none'   => [false, true, false, true],
            'willing and trained, not required'     => [true, true, true, false],
            'willing and trained, required'         => [true, true, true, true],
        ];
    }

    /**
     * @covers       \Engelsystem\Models\UserSupervisorStatus::canSupervise
     * @dataProvider canSuperviseDataProvider
     */
    public function testCanSupervise(bool $expected, bool $willing, bool $trained, bool $requireTraining): void
    {
        $model = new UserSupervisorStatus([
            'willing_to_supervise'           => $willing,
            'supervision_training_completed' => $trained,
        ]);

        $this->assertEquals($expected, $model->canSupervise($requireTraining));
    }

    /**
     * @covers \Engelsystem\Models\UserSupervisorStatus
     */
    public function testAttributeCasts(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $model = UserSupervisorStatus::create([
            'user_id'                        => $user->id,
            'willing_to_supervise'           => '1',
            'supervision_training_completed' => '0',
        ]);

        $model->refresh();

        $this->assertIsInt($model->user_id);
        $this->assertIsBool($model->willing_to_supervise);
        $this->assertIsBool($model->supervision_training_completed);
    }

    /**
     * @covers \Engelsystem\Models\UserSupervisorStatus
     */
    public function testDefaultAttributes(): void
    {
        $model = new UserSupervisorStatus();

        $this->assertFalse($model->willing_to_supervise);
        $this->assertFalse($model->supervision_training_completed);
    }

    /**
     * @covers \Engelsystem\Models\UserSupervisorStatus
     */
    public function testTimestamps(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $model = UserSupervisorStatus::create([
            'user_id' => $user->id,
        ]);

        $this->assertNotNull($model->created_at);
        $this->assertNotNull($model->updated_at);
    }
}
