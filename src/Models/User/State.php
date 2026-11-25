<?php

declare(strict_types=1);

namespace Engelsystem\Models\User;

use Carbon\Carbon;
use Engelsystem\Database\LegacySchema;
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
 * @property-read bool   $force_food
 * @property int|null    $force_food_by
 * @property-read bool   $got_goodie
 * @property int|null    $got_goodie_by
 * @property-read bool   $reserved_goodie
 * @property int|null    $reserved_goodie_by
 * @property int         $got_voucher
 *
 * @method static QueryBuilder|State[] whereArrived($value)
 * @method static QueryBuilder|State[] whereArrivalDate($value)
 * @method static QueryBuilder|State[] whereUserInfo($value)
 * @method static QueryBuilder|State[] whereActive($value)
 * @method static QueryBuilder|State[] whereForceActive($value)
 * @method static QueryBuilder|State[] whereForceActiveBy($value)
 * @method static QueryBuilder|State[] whereForceFood($value)
 * @method static QueryBuilder|State[] whereForceFoodBy($value)
 * @method static QueryBuilder|State[] whereGotGoodie($value)
 * @method static QueryBuilder|State[] whereGotGoodieBy($value)
 * @method static QueryBuilder|State[] whereReservedGoodie($value)
 * @method static QueryBuilder|State[] whereReservedGoodieBy($value)
 * @method static QueryBuilder|State[] whereGotVoucher($value)
 */
class State extends HasUserModel
{
    use HasFactory;

    /** @var string The table associated with the model */
    protected $table = 'users_state'; // phpcs:ignore

    /** @var array<string, bool|int|null> Default attributes */
    protected $attributes = [ // phpcs:ignore
        'arrival_date'    => null,
        'user_info'       => null,
        'active'          => false,
        'force_active_by' => null,
        'force_food_by'   => null,
        'got_goodie_by'   => null,
        'reserved_goodie_by'   => null,
        'got_voucher'     => 0,
    ];

    /** @var array<string, string> */
    protected $casts = [ // phpcs:ignore
        'user_id'      => 'integer',
        'arrival_date' => 'datetime',
        'active'       => 'boolean',
        'force_active_by' => 'integer',
        'force_food_by'   => 'integer',
        'reserved_goodie_by' => 'integer',
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
        'force_food_by',
        'got_goodie_by',
        'reserved_goodie_by',
        'got_voucher',
    ];

    public function __construct(array $attributes = [])
    {
        if (LegacySchema::enabled()) {
            unset(
                $this->attributes['force_active_by'],
                $this->attributes['force_food_by'],
                $this->attributes['got_goodie_by'],
                $this->attributes['reserved_goodie_by'],
            );
            $this->attributes += ['arrived' => false, 'force_active' => false, 'got_goodie' => false];
        }

        parent::__construct($attributes);
    }

    public function setArrivalDateAttribute(mixed $value): void
    {
        if (LegacySchema::enabled()) {
            $this->attributes['arrived'] = $value !== null;
        }

        $this->attributes['arrival_date'] = $value === null ? null : $this->fromDateTime($value);
    }

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
        if (LegacySchema::enabled()) {
            return (bool) ($this->attributes['force_active'] ?? false);
        }

        return $this->force_active_by !== null;
    }

    public function setForceActiveByAttribute(mixed $userId): void
    {
        if (LegacySchema::enabled()) {
            $this->attributes['force_active'] = $userId !== null;
            return;
        }

        $this->attributes['force_active_by'] = $userId;
    }

    public static function forceActiveExpression(): string
    {
        return LegacySchema::enabled() ? 'users_state.force_active' : 'users_state.force_active_by IS NOT NULL';
    }

    /**
     * provide WhereForceActive query scope
     */
    public static function scopeWhereForceActive(Builder $query, bool $value): Builder
    {
        if (LegacySchema::enabled()) {
            return $query->where('force_active', $value);
        }

        return $value
            ? $query->whereNotNull('force_active_by')
            : $query->whereNull('force_active_by');
    }

    public function forceFoodBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'force_food_by');
    }

    /**
     * Accessor: for forced_food property
     * Derived from forced_food_by being not null
     */
    public function getForceFoodAttribute(): bool
    {
        return $this->force_food_by !== null;
    }

    /**
     * provide WhereForceFood query scope
     */
    public static function scopeWhereForceFood(Builder $query, bool $value): Builder
    {
        if (LegacySchema::enabled()) {
            return $value ? $query->whereRaw('1 = 0') : $query;
        }

        return $value
            ? $query->whereNotNull('force_food_by')
            : $query->whereNull('force_food_by');
    }

    public function gotGoodieBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'got_goodie_by');
    }

    /**
     * Accessor: for got_goodie property
     * Derived from got_goodie_by being not null
     */
    public function getGotGoodieAttribute(): bool
    {
        if (LegacySchema::enabled()) {
            return (bool) ($this->attributes['got_goodie'] ?? false);
        }

        return $this->got_goodie_by !== null;
    }

    public function setGotGoodieByAttribute(mixed $userId): void
    {
        if (LegacySchema::enabled()) {
            $this->attributes['got_goodie'] = $userId !== null;
            return;
        }

        $this->attributes['got_goodie_by'] = $userId;
    }

    /**
     * provide WhereGotGoodie query scope
     */
    public static function scopeWhereGotGoodie(Builder $query, bool $value): Builder
    {
        if (LegacySchema::enabled()) {
            return $query->where('got_goodie', $value);
        }

        return $value
            ? $query->whereNotNull('got_goodie_by')
            : $query->whereNull('got_goodie_by');
    }

    public function reservedGoodieBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reserved_goodie_by');
    }

    /**
     * Accessor: for reserved_goodie property
     * Derived from reserved_goodie_by being not null
     */
    public function getReservedGoodieAttribute(): bool
    {
        return $this->reserved_goodie_by !== null;
    }

    /**
     * provide WhereReservedGoodie query scope
     */
    public static function scopeWhereReservedGoodie(Builder $query, bool $value): Builder
    {
        if (LegacySchema::enabled()) {
            return $value ? $query->whereRaw('1 = 0') : $query;
        }

        return $value
            ? $query->whereNotNull('reserved_goodie_by')
            : $query->whereNull('reserved_goodie_by');
    }

    /**
     * Accessor: for arrived property
     * Derived from arrival_date being not null
     */
    public function getArrivedAttribute(): bool
    {
        if (LegacySchema::enabled()) {
            return (bool) ($this->attributes['arrived'] ?? false);
        }

        return $this->arrival_date !== null;
    }

    public static function arrivedExpression(): string
    {
        return LegacySchema::enabled() ? 'users_state.arrived' : 'users_state.arrival_date IS NOT NULL';
    }

    /**
     * provide WhereArrived query scope
     */
    public static function scopeWhereArrived(Builder $query, bool $value): Builder
    {
        if (LegacySchema::enabled()) {
            return $query->where('arrived', $value);
        }

        return $value
            ? $query->whereNotNull('arrival_date')
            : $query->whereNull('arrival_date');
    }
}
