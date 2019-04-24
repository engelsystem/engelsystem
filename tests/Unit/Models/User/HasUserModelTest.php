<?php

namespace Engelsystem\Test\Unit\Models;

use Engelsystem\Models\User\HasUserModel;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\Models\User\Stub\HasUserModelImplementation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use PHPUnit\Framework\TestCase;

class HasUserModelTest extends TestCase
{
    use HasDatabase;

    /**
     * @covers \Engelsystem\Models\User\HasUserModel::user
     */
    public function testHasOneRelations()
    {
        /** @var HasUserModel $contact */
        $model = new HasUserModelImplementation();

        $this->assertInstanceOf(BelongsTo::class, $model->user());
    }

    /**
     * Prepare test
     */
    protected function setUp(): void
    {
        $this->initDatabase();
    }
}
