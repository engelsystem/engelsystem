<?php

declare(strict_types=1);

namespace Engelsystem\Models\User;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
    use HasFactory;

    /** @var bool enable timestamps for created_at */
    public $timestamps = true; // phpcs:ignore

    /** @var null Disable updated_at */
    public const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [ // phpcs:ignore
        'user_id',
        'token',
    ];
}
