<?php

declare(strict_types=1);

namespace Engelsystem\Models;

use Carbon\Carbon;
use Engelsystem\Models\User\User;
use Engelsystem\Models\User\UsesUserModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property string    $id
 * @property string    $payload
 * @property int|null  $user_id
 * @property Carbon    $last_activity
 *
 * @property-read User|null $user
 *
 * @method static Builder|Session whereId($value)
 * @method static Builder|Session wherePayload($value)
 * @method static Builder|Session whereLastActivity($value)
 */
class Session extends BaseModel
{
    use HasFactory;
    use UsesUserModel;

    public $incrementing = false; // phpcs:ignore

    protected $keyType = 'string'; // phpcs:ignore

    /** @var array<string, string|null> default attributes */
    protected $attributes = [ // phpcs:ignore
        'payload' => '',
        'user_id' => null,
    ];

    /** @var array<string> */
    protected $fillable = [ // phpcs:ignore
        'id',
        'payload',
        'user_id',
    ];

    /** @var array<string, string> */
    protected $casts = [ // phpcs:ignore
        'user_id' => 'integer',
        'last_activity' => 'datetime',
    ];
}
