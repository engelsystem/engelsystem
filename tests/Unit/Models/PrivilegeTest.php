<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Models;

use Engelsystem\Models\Group;
use Engelsystem\Models\Privilege;

class PrivilegeTest extends ModelTest
{
    /**
     * @covers \Engelsystem\Models\Privilege::groups
     */
    public function testGroups(): void
    {
        /** @var Group $group1 */
        $group1 = Group::factory()->create();
        /** @var Group $group2 */
        $group2 = Group::factory()->create();

        $model = new Privilege();
        $model->name = 'Some Privilege';
        $model->description = 'Some long description';
        $model->save();

        $model->groups()->attach($group1);
        $model->groups()->attach($group2);

        /** @var Privilege $savedModel */
        $savedModel = Privilege::whereName('Some Privilege')->first();
        $this->assertEquals('Some Privilege', $savedModel->name);
        $this->assertEquals('Some long description', $savedModel->description);
        $this->assertEquals($group1->name, $savedModel->groups[0]->name);
        $this->assertEquals($group2->name, $savedModel->groups[1]->name);
    }
}
