<?php

namespace Engelsystem\Test\Unit\Models;

use Engelsystem\Test\Unit\Models\Stub\BaseModelImplementation;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use PHPUnit\Framework\MockObject\MockObject;
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

    /**
     * @covers \Engelsystem\Models\BaseModel::find
     */
    public function testFind()
    {
        /** @var QueryBuilder|MockObject $queryBuilder */
        $queryBuilder = $this->createMock(QueryBuilder::class);
        BaseModelImplementation::$queryBuilder = $queryBuilder;

        $anotherModel = new BaseModelImplementation();

        $queryBuilder->expects($this->once())
            ->method('find')
            ->with(1337, ['foo', 'bar'])
            ->willReturn($anotherModel);

        $model = new BaseModelImplementation();
        $newModel = $model->find(1337, ['foo', 'bar']);

        $this->assertEquals($anotherModel, $newModel);
    }

    /**
     * @covers \Engelsystem\Models\BaseModel::findOrNew
     */
    public function testFindOrNew()
    {
        /** @var QueryBuilder|MockObject $queryBuilder */
        $queryBuilder = $this->createMock(QueryBuilder::class);
        BaseModelImplementation::$queryBuilder = $queryBuilder;

        $anotherModel = new BaseModelImplementation();

        $queryBuilder->expects($this->once())
            ->method('findOrNew')
            ->with(31337, ['lorem', 'ipsum'])
            ->willReturn($anotherModel);

        $model = new BaseModelImplementation();
        $newModel = $model->findOrNew(31337, ['lorem', 'ipsum']);

        $this->assertEquals($anotherModel, $newModel);
    }
}
