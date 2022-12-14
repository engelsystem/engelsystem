<?php

namespace Engelsystem\Test\Unit\Models\User;

use Engelsystem\Models\BaseModel;
use Engelsystem\Models\User\UsesUserModel;
use Engelsystem\Test\Unit\Models\ModelTest;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsesUserModelTest extends ModelTest
{
    /**
     * @covers \Engelsystem\Models\User\UsesUserModel::user
     */
    public function testHasOneRelations(): void
    {
        /** @var UsesUserModel $contact */
        $model = new class extends BaseModel
        {
            use UsesUserModel;
        };

        $this->assertInstanceOf(BelongsTo::class, $model->user());
    }
}
