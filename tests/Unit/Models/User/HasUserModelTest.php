<?php

namespace Engelsystem\Test\Unit\Models;

use Engelsystem\Models\User\HasUserModel;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\Models\User\Stub\HasUserModelImplementation;
use Engelsystem\Test\Unit\TestCase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        parent::setUp();
        $this->initDatabase();
    }
}
