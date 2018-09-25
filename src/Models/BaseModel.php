<?php

namespace Engelsystem\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    /** @var bool Disable timestamps by default because of "Datensparsamkeit" */
    public $timestamps = false;

    /**
     * Create a new model
     *
     * @param array $attributes
     * @return BaseModel
     */
    public function create(array $attributes = [])
    {
        $instance = new static($attributes);
        $instance->save();

        return $instance;
    }

    /**
     * Find a model by its primary key
     *
     * @param mixed $id
     * @param array $columns
     * @return Builder|Builder[]|Collection|Model|null
     */
    public static function find($id, $columns = ['*'])
    {
        return static::query()->find($id, $columns);
    }

    /**
     * Find a model by its attributes or create a new one
     *
     * @param mixed $id
     * @param array $columns
     * @return static|Model
     */
    public static function findOrNew($id, $columns = ['*'])
    {
        return static::query()->findOrNew($id, $columns);
    }
}
