<?php

declare(strict_types=1);

namespace Engelsystem\Models;

use Carbon\Carbon;
use Engelsystem\Models\User\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @mixin Builder
 *
 * @property int         $id
 * @property int         $minor_user_id
 * @property int         $guardian_user_id
 * @property bool        $is_primary
 * @property string      $relationship_type
 * @property bool        $can_manage_account
 * @property Carbon|null $valid_from
 * @property Carbon|null $valid_until
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read User   $minor
 * @property-read User   $guardian
 * @property-read bool   $isCurrentlyValid
 *
 * @method static QueryBuilder|UserGuardian[] whereId($value)
 * @method static QueryBuilder|UserGuardian[] whereMinorUserId($value)
 * @method static QueryBuilder|UserGuardian[] whereGuardianUserId($value)
 * @method static QueryBuilder|UserGuardian[] whereIsPrimary($value)
 * @method static Builder|UserGuardian valid()
 * @method static Builder|UserGuardian primary()
 */
class UserGuardian extends BaseModel
{
    use HasFactory;

    /** @var string */
    protected $table = 'user_guardian'; // phpcs:ignore

    /** @var bool enable timestamps */
    public $timestamps = true; // phpcs:ignore

    /** @var array<string, mixed> default attributes */
    protected $attributes = [ // phpcs:ignore
        'is_primary'         => false,
        'relationship_type'  => 'parent',
        'can_manage_account' => true,
        'valid_from'         => null,
        'valid_until'        => null,
    ];

    /** @var array<string> */
    protected $fillable = [ // phpcs:ignore
        'minor_user_id',
        'guardian_user_id',
        'is_primary',
        'relationship_type',
        'can_manage_account',
        'valid_from',
        'valid_until',
    ];

    /** @var array<string, string> */
    protected $casts = [ // phpcs:ignore
        'minor_user_id'      => 'integer',
        'guardian_user_id'   => 'integer',
        'is_primary'         => 'boolean',
        'can_manage_account' => 'boolean',
        'valid_from'         => 'datetime',
        'valid_until'        => 'datetime',
    ];

    public function minor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'minor_user_id');
    }

    public function guardian(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guardian_user_id');
    }

    public function getIsCurrentlyValidAttribute(): bool
    {
        $now = Carbon::now();

        if ($this->valid_from !== null && $now->lt($this->valid_from)) {
            return false;
        }

        if ($this->valid_until !== null && $now->gt($this->valid_until)) {
            return false;
        }

        return true;
    }

    /**
     * Scope to filter only currently valid guardian relationships
     */
    public function scopeValid(Builder $query): Builder
    {
        return $query->where(function (Builder $q): void {
            $q->whereNull('valid_from')
                ->orWhere('valid_from', '<=', Carbon::now());
        })->where(function (Builder $q): void {
            $q->whereNull('valid_until')
                ->orWhere('valid_until', '>=', Carbon::now());
        });
    }

    /**
     * Scope to filter only primary guardians
     */
    public function scopePrimary(Builder $query): Builder
    {
        return $query->where('is_primary', true);
    }
}
