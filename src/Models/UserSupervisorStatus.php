<?php

declare(strict_types=1);

namespace Engelsystem\Models;

use Carbon\Carbon;
use Engelsystem\Models\User\User;
use Engelsystem\Models\User\UsesUserModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @mixin Builder
 *
 * @property int         $id
 * @property int         $user_id
 * @property bool        $willing_to_supervise
 * @property bool        $supervision_training_completed
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read User   $user
 *
 * @method static QueryBuilder|UserSupervisorStatus[] whereId($value)
 * @method static QueryBuilder|UserSupervisorStatus[] whereUserId($value)
 * @method static QueryBuilder|UserSupervisorStatus[] whereWillingToSupervise($value)
 * @method static Builder|UserSupervisorStatus willing()
 * @method static Builder|UserSupervisorStatus trained()
 */
class UserSupervisorStatus extends BaseModel
{
    use HasFactory;
    use UsesUserModel;

    /** @var string */
    protected $table = 'user_supervisor_status'; // phpcs:ignore

    /** @var bool enable timestamps */
    public $timestamps = true; // phpcs:ignore

    /** @var array<string, mixed> default attributes */
    protected $attributes = [ // phpcs:ignore
        'willing_to_supervise'           => false,
        'supervision_training_completed' => false,
    ];

    /** @var array<string> */
    protected $fillable = [ // phpcs:ignore
        'user_id',
        'willing_to_supervise',
        'supervision_training_completed',
    ];

    /** @var array<string, string> */
    protected $casts = [ // phpcs:ignore
        'user_id'                        => 'integer',
        'willing_to_supervise'           => 'boolean',
        'supervision_training_completed' => 'boolean',
    ];

    /**
     * Scope to filter users willing to supervise
     */
    public function scopeWilling(Builder $query): Builder
    {
        return $query->where('willing_to_supervise', true);
    }

    /**
     * Scope to filter users who completed training
     */
    public function scopeTrained(Builder $query): Builder
    {
        return $query->where('supervision_training_completed', true);
    }

    /**
     * Check if the user can supervise (willing and optionally trained)
     */
    public function canSupervise(bool $requireTraining = false): bool
    {
        if (!$this->willing_to_supervise) {
            return false;
        }

        if ($requireTraining && !$this->supervision_training_completed) {
            return false;
        }

        return true;
    }
}
