<?php

declare(strict_types=1);

namespace Engelsystem\Models;

use Carbon\Carbon;
use Engelsystem\Models\User\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @mixin Builder
 *
 * @property int              $id
 * @property string           $name
 * @property string|null      $description
 * @property int|null         $min_shift_start_hour
 * @property int|null         $max_shift_end_hour
 * @property int|null         $max_hours_per_day
 * @property array            $allowed_work_categories
 * @property bool             $can_fill_slot
 * @property bool             $requires_supervisor
 * @property bool             $can_self_signup
 * @property int              $display_order
 * @property bool             $is_active
 * @property Carbon|null      $created_at
 * @property Carbon|null      $updated_at
 *
 * @property-read Collection<User> $users
 *
 * @method static QueryBuilder|MinorCategory[] whereId($value)
 * @method static QueryBuilder|MinorCategory[] whereName($value)
 * @method static QueryBuilder|MinorCategory[] whereIsActive($value)
 */
class MinorCategory extends BaseModel
{
    use HasFactory;

    /** @var bool enable timestamps */
    public $timestamps = true; // phpcs:ignore

    /** @var array<string, mixed> default attributes */
    protected $attributes = [ // phpcs:ignore
        'description'             => null,
        'min_shift_start_hour'    => null,
        'max_shift_end_hour'      => null,
        'max_hours_per_day'       => null,
        'can_fill_slot'           => true,
        'requires_supervisor'     => true,
        'can_self_signup'         => true,
        'display_order'           => 0,
        'is_active'               => true,
    ];

    /** @var array<string> */
    protected $fillable = [ // phpcs:ignore
        'name',
        'description',
        'min_shift_start_hour',
        'max_shift_end_hour',
        'max_hours_per_day',
        'allowed_work_categories',
        'can_fill_slot',
        'requires_supervisor',
        'can_self_signup',
        'display_order',
        'is_active',
    ];

    /** @var array<string, string> */
    protected $casts = [ // phpcs:ignore
        'min_shift_start_hour'    => 'integer',
        'max_shift_end_hour'      => 'integer',
        'max_hours_per_day'       => 'integer',
        'allowed_work_categories' => 'array',
        'can_fill_slot'           => 'boolean',
        'requires_supervisor'     => 'boolean',
        'can_self_signup'         => 'boolean',
        'display_order'           => 'integer',
        'is_active'               => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'minor_category_id');
    }

    public function allowsWorkCategory(string $category): bool
    {
        return in_array($category, $this->allowed_work_categories, true);
    }

    /**
     * Scope to filter only active categories
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    protected static function boot(): void
    {
        parent::boot();
        static::addGlobalScope('order', fn(Builder $builder) => $builder->orderBy('display_order'));
    }
}
