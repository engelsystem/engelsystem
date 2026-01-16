<?php

declare(strict_types=1);

namespace Engelsystem\Models\Shifts;

use Engelsystem\Models\BaseModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @property int                        $id
 * @property string                     $name
 * @property string                     $description
 * @property string                     $work_category
 * @property bool                       $allows_accompanying_children
 * @property float|null                 $signup_advance_hours
 *
 * @property-read Collection|NeededAngelType[] $neededAngelTypes
 * @property-read Collection|Schedule[] $schedules
 * @property-read Collection|Shift[]    $shifts
 *
 * @method static QueryBuilder|ShiftType[] whereId($value)
 * @method static QueryBuilder|ShiftType[] whereName($value)
 * @method static QueryBuilder|ShiftType[] whereDescription($value)
 * @method static QueryBuilder|ShiftType[] whereWorkCategory($value)
 * @method static QueryBuilder|ShiftType[] whereAllowsAccompanyingChildren($value)
 * @method static QueryBuilder|ShiftType[] whereSignupAdvanceHours($value)
 */
class ShiftType extends BaseModel
{
    use HasFactory;

    /** @var array<string> */
    protected $fillable = [ // phpcs:ignore
        'name',
        'description',
        'work_category',
        'allows_accompanying_children',
        'signup_advance_hours',
    ];

    /** @var array<string, mixed> default attributes */
    protected $attributes = [ // phpcs:ignore
        'work_category' => 'A',
        'allows_accompanying_children' => false,
        'signup_advance_hours' => null,
    ];

    /** @var array<string, string> */
    protected $casts = [ // phpcs:ignore
        'allows_accompanying_children' => 'boolean',
    ];

    public function neededAngelTypes(): HasMany
    {
        return $this->hasMany(NeededAngelType::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'shift_type');
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }
}
