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
 * @property float|null                 $signup_advance_hours
 *
 * @property-read Collection|NeededAngelType[] $neededAngelTypes
 * @property-read Collection|Schedule[] $schedules
 * @property-read Collection|Shift[]    $shifts
 *
 * @method static QueryBuilder|ShiftType[] whereId($value)
 * @method static QueryBuilder|ShiftType[] whereName($value)
 * @method static QueryBuilder|ShiftType[] whereDescription($value)
 * @method static QueryBuilder|ShiftType[] whereSignupAdvanceHours($value)
 */
class ShiftType extends BaseModel
{
    use HasFactory;

    /** @var array<string> */
    protected $fillable = [ // phpcs:ignore
        'name',
        'description',
        'signup_advance_hours',
    ];

    /** @var array<string, null> default attributes */
    protected $attributes = [ // phpcs:ignore
        'signup_advance_hours' => null,
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
