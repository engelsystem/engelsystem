<?php

namespace Engelsystem\Models\User;

use Engelsystem\Models\BaseModel;

abstract class HasUserModel extends BaseModel
{
    use UsesUserModel;

    /** @var string The primary key for the model */
    protected $primaryKey = 'user_id'; // phpcs:ignore

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [ // phpcs:ignore
        'user_id',
    ];

    /** @var array<string> */
    protected $casts = [ // phpcs:ignore
        'user_id' => 'integer',
    ];

    /**
     * The relationships that should be touched on save
     *
     * @var array<string>
     */
    protected $touches = ['user']; // phpcs:ignore
}
