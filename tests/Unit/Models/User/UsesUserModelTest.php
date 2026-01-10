<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Models\User;

use Engelsystem\Models\BaseModel;
use Engelsystem\Models\User\UsesUserModel;
use Engelsystem\Test\Unit\Models\ModelTestCase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(UsesUserModel::class, 'user')]
class UsesUserModelTest extends ModelTestCase
{
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
