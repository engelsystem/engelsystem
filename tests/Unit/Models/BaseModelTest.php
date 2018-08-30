<?php

namespace Engelsystem\Test\Unit\Models;

use Engelsystem\Test\Unit\Models\Stub\BaseModelImplementation;
use PHPUnit\Framework\TestCase;

class BaseModelTest extends TestCase
{
    /**
     * @covers \Engelsystem\Models\BaseModel::create
     */
    public function testCreate()
    {
        $model = new BaseModelImplementation();
        $newModel = $model->create(['foo' => 'bar']);

        $this->assertNotEquals($model, $newModel);
        $this->assertEquals('bar', $newModel->foo);
        $this->assertEquals(1, $newModel->saveCount);
    }
}
