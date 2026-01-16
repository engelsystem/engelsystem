<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Models;

use Carbon\Carbon;
use Engelsystem\Models\User\User;
use Engelsystem\Models\UserGuardian;

/**
 * @covers \Engelsystem\Models\UserGuardian
 */
class UserGuardianTest extends ModelTest
{
    /**
     * @covers \Engelsystem\Models\UserGuardian::minor
     * @covers \Engelsystem\Models\UserGuardian::guardian
     */
    public function testRelationships(): void
    {
        /** @var User $minor */
        $minor = User::factory()->create();
        /** @var User $guardian */
        $guardian = User::factory()->create();

        $model = new UserGuardian();
        $model->minor()->associate($minor);
        $model->guardian()->associate($guardian);
        $model->save();

        $model = UserGuardian::find(1);
        $this->assertEquals($minor->id, $model->minor->id);
        $this->assertEquals($guardian->id, $model->guardian->id);
    }

    /**
     * @return array<string, array{bool, Carbon|null, Carbon|null}>
     */
    public function isCurrentlyValidDataProvider(): array
    {
        return [
            'no dates set'                  => [true, null, null],
            'valid_from in past'            => [true, Carbon::now()->subDay(), null],
            'valid_from now'                => [true, Carbon::now(), null],
            'valid_from in future'          => [false, Carbon::now()->addDay(), null],
            'valid_until in past'           => [false, null, Carbon::now()->subDay()],
            'valid_until in near future'    => [true, null, Carbon::now()->addMinute()],
            'valid_until in future'         => [true, null, Carbon::now()->addDay()],
            'both in valid range'           => [true, Carbon::now()->subDay(), Carbon::now()->addDay()],
            'both but from in future'       => [false, Carbon::now()->addDay(), Carbon::now()->addWeek()],
            'both but until in past'        => [false, Carbon::now()->subWeek(), Carbon::now()->subDay()],
        ];
    }

    /**
     * @covers       \Engelsystem\Models\UserGuardian::getIsCurrentlyValidAttribute
     * @dataProvider isCurrentlyValidDataProvider
     */
    public function testGetIsCurrentlyValidAttribute(bool $expected, ?Carbon $from, ?Carbon $until): void
    {
        /** @var User $minor */
        $minor = User::factory()->create();
        /** @var User $guardian */
        $guardian = User::factory()->create();

        $model = UserGuardian::create([
            'minor_user_id'    => $minor->id,
            'guardian_user_id' => $guardian->id,
            'valid_from'       => $from,
            'valid_until'      => $until,
        ]);

        $this->assertEquals($expected, $model->isCurrentlyValid);
    }

    /**
     * @covers \Engelsystem\Models\UserGuardian::scopeValid
     */
    public function testScopeValid(): void
    {
        /** @var User $minor */
        $minor = User::factory()->create();
        /** @var User $guardian1 */
        $guardian1 = User::factory()->create();
        /** @var User $guardian2 */
        $guardian2 = User::factory()->create();
        /** @var User $guardian3 */
        $guardian3 = User::factory()->create();
        /** @var User $guardian4 */
        $guardian4 = User::factory()->create();

        // Valid: no date restrictions
        UserGuardian::create([
            'minor_user_id'    => $minor->id,
            'guardian_user_id' => $guardian1->id,
        ]);

        // Valid: currently within range
        UserGuardian::create([
            'minor_user_id'    => $minor->id,
            'guardian_user_id' => $guardian2->id,
            'valid_from'       => Carbon::now()->subDay(),
            'valid_until'      => Carbon::now()->addDay(),
        ]);

        // Invalid: expired
        UserGuardian::create([
            'minor_user_id'    => $minor->id,
            'guardian_user_id' => $guardian3->id,
            'valid_from'       => Carbon::now()->subWeek(),
            'valid_until'      => Carbon::now()->subDay(),
        ]);

        // Invalid: not yet valid
        UserGuardian::create([
            'minor_user_id'    => $minor->id,
            'guardian_user_id' => $guardian4->id,
            'valid_from'       => Carbon::now()->addDay(),
            'valid_until'      => Carbon::now()->addWeek(),
        ]);

        $validGuardians = UserGuardian::valid()->get();
        $this->assertCount(2, $validGuardians);
        $this->assertContains($guardian1->id, $validGuardians->pluck('guardian_user_id')->toArray());
        $this->assertContains($guardian2->id, $validGuardians->pluck('guardian_user_id')->toArray());
    }

    /**
     * @covers \Engelsystem\Models\UserGuardian::scopePrimary
     */
    public function testScopePrimary(): void
    {
        /** @var User $minor */
        $minor = User::factory()->create();
        /** @var User $guardian1 */
        $guardian1 = User::factory()->create();
        /** @var User $guardian2 */
        $guardian2 = User::factory()->create();
        /** @var User $guardian3 */
        $guardian3 = User::factory()->create();

        UserGuardian::create([
            'minor_user_id'    => $minor->id,
            'guardian_user_id' => $guardian1->id,
            'is_primary'       => true,
        ]);
        UserGuardian::create([
            'minor_user_id'    => $minor->id,
            'guardian_user_id' => $guardian2->id,
            'is_primary'       => false,
        ]);
        UserGuardian::create([
            'minor_user_id'    => $minor->id,
            'guardian_user_id' => $guardian3->id,
            'is_primary'       => true,
        ]);

        $primaryGuardians = UserGuardian::primary()->get();
        $this->assertCount(2, $primaryGuardians);
    }

    /**
     * @covers \Engelsystem\Models\UserGuardian
     */
    public function testAttributeCasts(): void
    {
        /** @var User $minor */
        $minor = User::factory()->create();
        /** @var User $guardian */
        $guardian = User::factory()->create();

        $model = UserGuardian::create([
            'minor_user_id'      => $minor->id,
            'guardian_user_id'   => $guardian->id,
            'is_primary'         => '1',
            'can_manage_account' => '0',
            'valid_from'         => '2025-01-01 00:00:00',
            'valid_until'        => '2025-12-31 23:59:59',
        ]);

        $model->refresh();

        $this->assertIsInt($model->minor_user_id);
        $this->assertIsInt($model->guardian_user_id);
        $this->assertIsBool($model->is_primary);
        $this->assertIsBool($model->can_manage_account);
        $this->assertInstanceOf(Carbon::class, $model->valid_from);
        $this->assertInstanceOf(Carbon::class, $model->valid_until);
    }

    /**
     * @covers \Engelsystem\Models\UserGuardian
     */
    public function testDefaultAttributes(): void
    {
        $model = new UserGuardian();

        $this->assertFalse($model->is_primary);
        $this->assertEquals('parent', $model->relationship_type);
        $this->assertTrue($model->can_manage_account);
        $this->assertNull($model->valid_from);
        $this->assertNull($model->valid_until);
    }
}
