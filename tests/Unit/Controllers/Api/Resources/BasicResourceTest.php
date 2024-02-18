<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Api\Resources;

use Engelsystem\Controllers\Api\Resources\BasicResource;
use Engelsystem\Models\BaseModel;
use Engelsystem\Test\Unit\TestCase;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;
use Stringable;

class BasicResourceTest extends TestCase
{
    /**
     * @covers \Engelsystem\Controllers\Api\Resources\BasicResource::__construct
     * @covers \Engelsystem\Controllers\Api\Resources\BasicResource::toArray
     */
    public function testToArray(): void
    {
        $model = $this->getModel();
        $resource = $this->getResource($model);

        $this->assertInstanceOf(Arrayable::class, $resource);
        $this->assertEquals(['test' => 'value'], $resource->toArray());
    }

    /**
     * @covers \Engelsystem\Controllers\Api\Resources\BasicResource::toIdentifierArray
     */
    public function testToIdentifierArray(): void
    {
        $model = $this->getModel()->setAttribute('id', 42);
        $resource = $this->getResource($model);

        $this->assertEquals(['id' => 42], $resource->toIdentifierArray($model));

        $model = $model->setAttribute('name', 'test');
        $resource = $this->getResource($model);

        $this->assertEquals(['id' => 42, 'name' => 'test'], $resource->toIdentifierArray($model));
    }

    /**
     * @covers \Engelsystem\Controllers\Api\Resources\BasicResource::toJson
     */
    public function testToJson(): void
    {
        $model = $this->getModel();
        $resource = $this->getResource($model);

        $this->assertInstanceOf(Jsonable::class, $resource);
        $this->assertEquals('{"test":"value"}', $resource->toJson());
    }

    /**
     * @covers \Engelsystem\Controllers\Api\Resources\BasicResource::toJson
     */
    public function testToJsonOptions(): void
    {
        $resource = $this->getResource(new Collection());

        $this->assertInstanceOf(Jsonable::class, $resource);
        $this->assertEquals('{}', $resource->toJson(JSON_FORCE_OBJECT));
    }

    /**
     * @covers \Engelsystem\Controllers\Api\Resources\BasicResource::__toString
     */
    public function testToString(): void
    {
        $model = $this->getModel();
        $resource = $this->getResource($model);

        $this->assertInstanceOf(Stringable::class, $resource);
        $this->assertEquals('{"test":"value"}', (string) $resource);
    }

    /**
     * @covers \Engelsystem\Controllers\Api\Resources\BasicResource::collection
     */
    public function testCollection(): void
    {
        $resource = $this->getResource(new Collection());
        $modelA = $this->getModel();
        $modelB = $this->getModel()->setAttribute('test', 'B');
        $collection = $resource->collection([$modelA, $modelB]);

        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertCount(2, $collection);

        $this->assertInstanceOf(BasicResource::class, $collection->first());
        $this->assertEquals(['test' => 'value'], $collection->first()->toArray());

        $this->assertInstanceOf(BasicResource::class, $collection->last());
        $this->assertEquals(['test' => 'B'], $collection->last()->toArray());
    }

    protected function getResource(BaseModel|Collection $model): BasicResource
    {
        return new class ($model) extends BasicResource {
        };
    }


    protected function getModel(): BaseModel
    {
        $model = new class extends BaseModel {
        };
        $model->setAttribute('test', 'value');

        return $model;
    }
}
