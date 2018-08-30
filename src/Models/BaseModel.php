<?php

namespace Engelsystem\Models;

use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    /** @var bool Disable timestamps by default because of "Datensparsamkeit" */
    public $timestamps = false;

    /**
     * @param array $attributes
     * @return BaseModel
     */
    public function create(array $attributes = [])
    {
        $instance = new static($attributes);
        $instance->save();

        return $instance;
    }
}
