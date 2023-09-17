<?php

declare(strict_types=1);

namespace Engelsystem\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property string    $id
 * @property string    $payload
 * @property Carbon    $last_activity
 *
 * @method static Builder|Session whereId($value)
 * @method static Builder|Session wherePayload($value)
 * @method static Builder|Session whereLastActivity($value)
 */
class Session extends BaseModel
{
    use HasFactory;

    public $incrementing = false; // phpcs:ignore

    protected $keyType = 'string'; // phpcs:ignore

    /** @var array<string, string|null> default attributes */
    protected $attributes = [ // phpcs:ignore
        'payload' => '',
    ];

    /** @var array<string> */
    protected $dates = [ // phpcs:ignore
        'last_activity',
    ];

    /** @var array<string> */
    protected $fillable = [ // phpcs:ignore
        'id',
        'payload',
    ];
}
