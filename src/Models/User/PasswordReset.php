<?php

namespace Engelsystem\Models\User;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @property string      $token
 * @property Carbon|null $created_at
 *
 * @method static QueryBuilder|PasswordReset[] whereToken($value)
 * @method static QueryBuilder|PasswordReset[] whereCreatedAt($value)
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
