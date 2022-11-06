<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Models;

use Engelsystem\Models\Group;
use Engelsystem\Models\Privilege;
use Engelsystem\Models\User\User;

class GroupTest extends ModelTest
{
    /**
     * @covers \Engelsystem\Models\Group::privileges
     */
    public function testPrivileges(): void
    {
        /** @var Privilege $privilege1 */
        $privilege1 = Privilege::factory()->create();
        /** @var Privilege $privilege2 */
        $privilege2 = Privilege::factory()->create();

        $model = new Group();
        $model->name = 'Some Group';
        $model->save();

        $model->privileges()->attach($privilege1);
        $model->privileges()->attach($privilege2);

        /** @var Group $savedModel */
        $savedModel = Group::first();
        $this->assertEquals('Some Group', $savedModel->name);
        $this->assertEquals($privilege1->name, $savedModel->privileges[0]->name);
        $this->assertEquals($privilege2->name, $savedModel->privileges[1]->name);
    }

    /**
     * @covers \Engelsystem\Models\Group::users
     */
    public function testUsers(): void
    {
        /** @var User $user1 */
        $user1 = User::factory()->create();
        /** @var User $user2 = */
        $user2 = User::factory()->create();

        $model = new Group();
        $model->name = 'Some Group';
        $model->save();

        $model->users()->attach($user1);
        $model->users()->attach($user2);

        /** @var Group $savedModel */
        $savedModel = Group::first();
        $this->assertEquals($user1->name, $savedModel->users[0]->name);
        $this->assertEquals($user2->name, $savedModel->users[1]->name);
    }
}
