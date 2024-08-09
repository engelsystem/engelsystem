<?php

declare(strict_types=1);

namespace Engelsystem\Models;

use Carbon\Carbon;
use Engelsystem\Models\User\UsesUserModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @property int         $id
 * @property string      $provider
 * @property string      $identifier
 * @property string|null $access_token
 * @property string|null $refresh_token
 * @property Carbon|null $expires_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static QueryBuilder|OAuth[] whereId($value)
 * @method static QueryBuilder|OAuth[] whereProvider($value)
 * @method static QueryBuilder|OAuth[] whereIdentifier($value)
 * @method static QueryBuilder|OAuth[] whereAccessToken($value)
 * @method static QueryBuilder|OAuth[] whereRefreshToken($value)
 */
class OAuth extends BaseModel
{
    use HasFactory;
    use UsesUserModel;

    public $table = 'oauth'; // phpcs:ignore

    /** @var array<string, null> default attributes */
    protected $attributes = [ // phpcs:ignore
        'access_token'  => null,
        'refresh_token' => null,
        'expires_at'    => null,
    ];

    /** @var bool Enable timestamps */
    public $timestamps = true; // phpcs:ignore

    /** @var array<string, string> */
    protected $casts = [ // phpcs:ignore
        'user_id' => 'integer',
        'expires_at' => 'datetime',
    ];

    /** @var array<string> */
    protected $fillable = [ // phpcs:ignore
        'provider',
        'identifier',
        'access_token',
        'refresh_token',
        'expires_at',
    ];
}
