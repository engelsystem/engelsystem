<?php

namespace Engelsystem\Test\Unit\Controllers;

use Engelsystem\Test\Unit\Controllers\Stub\CleanupModelImplementation;
use Engelsystem\Test\Unit\Controllers\Stub\TestModel;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\TestCase;

class CleanupModelTest extends TestCase
{
    use HasDatabase;

    /**
     * @covers \Engelsystem\Controllers\CleanupModel::cleanupModelNullValues
     */
    public function testCleanupModelNullValues()
    {
        $cleanup = new CleanupModelImplementation();

        $model = new TestModel();
        $model->foo = null;
        $model2 = new TestModel();
        $model3 = new TestModel();
        $model4 = null;

        $cleanup->cleanup($model);
        $cleanup->cleanup([$model2]);
        $cleanup->cleanup($model3, ['text']);
        $cleanup->cleanup($model4);

        $this->assertTrue(isset($model->text));
        $this->assertTrue(isset($model->created_at));
        $this->assertTrue(isset($model->foo));
        $this->assertEquals('', $model->text);

        $this->assertTrue(isset($model2->text));

        $this->assertTrue(isset($model3->text));
        $this->assertNull($model3->another_text);
        $this->assertNull($model3->foo);

        $this->assertNull($model4);
    }

    /**
     * Setup the DB
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->initDatabase();
    }
}
