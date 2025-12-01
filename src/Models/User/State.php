<?php

declare(strict_types=1);

namespace Engelsystem\Models\User;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @property-read bool   $arrived
 * @property Carbon|null $arrival_date
 * @property string|null $user_info
 * @property bool        $active
 * @property-read bool   $force_active
 * @property int|null    $force_active_by
 * @property bool        $force_food
 * @property bool        $got_goodie
 * @property int         $got_voucher
 *
 * @method static QueryBuilder|State[] whereArrived($value)
 * @method static QueryBuilder|State[] whereArrivalDate($value)
 * @method static QueryBuilder|State[] whereUserInfo($value)
 * @method static QueryBuilder|State[] whereActive($value)
 * @method static QueryBuilder|State[] whereForceActive($value)
 * @method static QueryBuilder|State[] whereForceActiveBy($value)
 * @method static QueryBuilder|State[] whereForceFood($value)
 * @method static QueryBuilder|State[] whereGotGoodie($value)
 * @method static QueryBuilder|State[] whereGotVoucher($value)
 */
class State extends HasUserModel
{
    use HasFactory;

    /** @var string The table associated with the model */
    protected $table = 'users_state'; // phpcs:ignore

    /** @var array<string, bool|int|null> Default attributes */
    protected $attributes = [ // phpcs:ignore
        'arrival_date' => null,
        'user_info'    => null,
        'active'       => false,
        'force_active_by' => null,
        'force_food' => false,
        'got_goodie'   => false,
        'got_voucher'  => 0,
    ];

    /** @var array<string, string> */
    protected $casts = [ // phpcs:ignore
        'user_id'      => 'integer',
        'arrival_date' => 'datetime',
        'active'       => 'boolean',
        'force_active_by' => 'integer',
        'force_food'   => 'boolean',
        'got_goodie'   => 'boolean',
        'got_voucher'  => 'integer',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [ // phpcs:ignore
        'user_id',
        'arrival_date',
        'user_info',
        'active',
        'force_active_by',
        'force_food',
        'got_goodie',
        'got_voucher',
    ];

    public function forceActiveBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'force_active_by');
    }

    /**
     * Accessor: for forced_active property
     * Derived from forced_active_by being not null
     */
    public function getForceActiveAttribute(): bool
    {
        return $this->force_active_by !== null;
    }

    /**
     * provide WhereForceActive query scope
     */
    public static function scopeWhereForceActive(Builder $query, bool $value): Builder
    {
        return $value
            ? $query->whereNotNull('force_active_by')
            : $query->whereNull('force_active_by');
    }

    /**
     * Accessor: for arrived property
     * Derived from arrival_date being not null
     */
    public function getArrivedAttribute(): bool
    {
        return $this->arrival_date !== null;
    }

    /**
     * provide WhereArrived query scope
     */
    public static function scopeWhereArrived(Builder $query, bool $value): Builder
    {
        return $value
            ? $query->whereNotNull('arrival_date')
            : $query->whereNull('arrival_date');
    }
}
