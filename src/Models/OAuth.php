<?php

declare(strict_types=1);

namespace Engelsystem\Models;

use Carbon\Carbon;
use Engelsystem\Models\User\UsesUserModel;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @property int         $id
 * @property string      $provider
 * @property string      $identifier
 * @property string      $access_token
 * @property string      $refresh_token
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
    use UsesUserModel;

    public $table = 'oauth'; // phpcs:ignore

    /** @var bool Enable timestamps */
    public $timestamps = true; // phpcs:ignore

    /** @var array<string, string> */
    protected $casts = [ // phpcs:ignore
        'user_id' => 'integer',
    ];

    /** @var array<string> */
    protected $dates = [ // phpcs:ignore
        'expires_at',
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
