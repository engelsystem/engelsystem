<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Models;

use Engelsystem\Models\AngelType;
use Engelsystem\Models\User\User;
use Engelsystem\Models\UserAngelType;
use Engelsystem\Models\UserAngelTypeMembership;
use Illuminate\Database\Eloquent\Model;

class UserAngelTypeTest extends ModelTest
{
    protected User|Model $user;
    protected User|Model $member;
    protected User|Model $supporter;
    protected User|Model $unconfirmed;
    protected User|Model $confirmed;

    protected AngelType|Model $angelType;
    protected AngelType|Model $restrictedAngelType;

    /**
     * @covers \Engelsystem\Models\UserAngelType
     */
    public function testCreateDefault(): void
    {
        $model = new UserAngelType();
        $model->user()->associate($this->user);
        $model->angelType()->associate($this->angelType);
        $model->save();

        /** @var UserAngelType $model */
        $model = UserAngelType::find(1);

        $this->assertEquals($this->user->id, $model->user->id);
        $this->assertEquals($this->angelType->id, $model->angelType->id);
        $this->assertNull($model->confirmUser);
        $this->assertFalse($model->supporter);
    }

    /**
     * @covers \Engelsystem\Models\UserAngelType
     * @covers \Engelsystem\Models\UserAngelType::angelType
     * @covers \Engelsystem\Models\UserAngelType::confirmUser
     */
    public function testCreateAssociation(): void
    {
        $this->user
            ->userAngelTypes()
            ->attach($this->angelType, ['confirm_user_id' => $this->confirmed->id, 'supporter' => true]);

        /** @var UserAngelType $model */
        $model = UserAngelType::find(1);

        $this->assertEquals($this->user->id, $model->user->id);
        $this->assertEquals($this->angelType->id, $model->angelType->id);
        $this->assertEquals($this->confirmed->id, $model->confirmUser->id);
        $this->assertTrue($model->supporter);
    }

    /**
     * @covers \Engelsystem\Models\UserAngelType::confirmUser
     */
    public function testConfirmUser(): void
    {
        $model = new UserAngelType();
        $model->user()->associate($this->user);
        $model->angelType()->associate($this->angelType);
        $model->confirmUser()->associate($this->confirmed);
        $model->save();

        /** @var UserAngelType $model */
        $model = UserAngelType::find(1);
        $this->assertEquals($this->confirmed->id, $model->confirmUser->id);
    }

    /**
     * @covers \Engelsystem\Models\UserAngelType::angelType
     */
    public function testAngelType(): void
    {
        $model = new UserAngelType();
        $model->user()->associate($this->user);
        $model->angelType()->associate($this->angelType);
        $model->save();

        /** @var UserAngelType $model */
        $model = UserAngelType::find(1);
        $this->assertEquals($this->angelType->id, $model->angelType->id);
    }

    /**
     * @covers \Engelsystem\Models\UserAngelType::getPivotAttributes
     */
    public function testGetPivotAttributes(): void
    {
        $attributes = UserAngelType::getPivotAttributes();

        $this->assertContains('id', $attributes);
        $this->assertContains('supporter', $attributes);
        $this->assertContains('confirm_user_id', $attributes);
    }

    /**
     * @covers \Engelsystem\Models\UserAngelType::getIsConfirmedAttribute
     */
    public function testGetIsConfirmedAttribute(): void
    {
        $this->angelType->restricted = false;
        $this->angelType->save();

        $model = new UserAngelType();
        $model->user()->associate($this->user);
        $model->angelType()->associate($this->angelType);
        $model->save();

        /** @var UserAngelType $model */
        $model = UserAngelType::find(1);
        $this->assertTrue($model->isConfirmed);

        $this->angelType->restricted = true;
        $this->angelType->save();
        /** @var UserAngelType $model */
        $model = UserAngelType::find(1);
        $this->assertFalse($model->isConfirmed);

        $model->confirmUser()->associate($this->confirmed);
        $model->save();
        /** @var UserAngelType $model */
        $model = UserAngelType::find(1);
        $this->assertTrue($model->isConfirmed);
    }

    /**
     * @covers \Engelsystem\Models\UserAngelType::getMembershipAttribute
     */
    public function testGetMembershipAttribute(): void
    {
        $this->supporter
            ->userAngelTypes()
            ->attach($this->angelType, ['confirm_user_id' => $this->confirmed->id, 'supporter' => true]);
        $this->member
            ->userAngelTypes()
            ->attach($this->restrictedAngelType, ['confirm_user_id' => $this->confirmed->id, 'supporter' => false]);
        $this->unconfirmed
            ->userAngelTypes()
            ->attach($this->restrictedAngelType, ['confirm_user_id' => null, 'supporter' => false]);
        $this->user
            ->userAngelTypes()
            ->attach($this->angelType, ['confirm_user_id' => null, 'supporter' => false]);

        /** @var UserAngelType $unconfirmed */
        $unconfirmed = UserAngelType::whereUserId($this->unconfirmed->id)->first();
        /** @var UserAngelType $confirmed */
        $confirmed = UserAngelType::whereUserId($this->member->id)->first();
        /** @var UserAngelType $supporter */
        $supporter = UserAngelType::whereUserId($this->supporter->id)->first();
        /** @var UserAngelType $unrestricted */
        $unrestricted = UserAngelType::whereUserId($this->user->id)->first();

        $this->assertEquals(UserAngelTypeMembership::SUPPORTER, $supporter->membership);
        $this->assertEquals(UserAngelTypeMembership::MEMBER, $confirmed->membership);
        $this->assertEquals(UserAngelTypeMembership::UNCONFIRMED, $unconfirmed->membership);
        $this->assertEquals(UserAngelTypeMembership::MEMBER, $unrestricted->membership);
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['id' => 42]);
        $this->confirmed = User::factory()->create(['id' => 1337]);
        $this->member = User::factory()->create();
        $this->supporter = User::factory()->create();
        $this->unconfirmed = User::factory()->create();
        $this->angelType = AngelType::factory()->create(['id' => 21, 'restricted' => false]);
        $this->restrictedAngelType = AngelType::factory()->create(['restricted' => true]);
    }
}
