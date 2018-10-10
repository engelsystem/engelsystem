<?php

namespace Engelsystem\Models\User;

/**
 * @property string         $token
 * @property \Carbon\Carbon $created_at
 *
 * @method static \Illuminate\Database\Query\Builder|\Engelsystem\Models\User\PasswordReset[] whereToken($value)
 * @method static \Illuminate\Database\Query\Builder|\Engelsystem\Models\User\PasswordReset[] whereCreatedAt($value)
 */
class PasswordReset extends HasUserModel
{
    /** @var bool enable timestamps for created_at */
    public $timestamps = true;

    /** @var null Disable updated_at */
    const UPDATED_AT = null;

    /** The attributes that are mass assignable */
    protected $fillable = [
        'user_id',
        'token',
    ];
}
