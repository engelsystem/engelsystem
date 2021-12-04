<?php

namespace Engelsystem\Models\User;

use Engelsystem\Models\BaseModel;

abstract class HasUserModel extends BaseModel
{
    use UsesUserModel;

    /** @var string The primary key for the model */
    protected $primaryKey = 'user_id';

    /** The attributes that are mass assignable */
    protected $fillable = [
        'user_id',
    ];

    /** @var string[] */
    protected $casts = [
        'user_id' => 'integer',
    ];

    /** The relationships that should be touched on save */
    protected $touches = ['user'];
}
