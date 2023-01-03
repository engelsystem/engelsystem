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
 *
 * @property-read Collection|Schedule[] $schedules
 * @property-read Collection|Shift[]    $shifts
 *
 * @method static QueryBuilder|ShiftType[] whereId($value)
 * @method static QueryBuilder|ShiftType[] whereName($value)
 * @method static QueryBuilder|ShiftType[] whereDescription($value)
 */
class ShiftType extends BaseModel
{
    use HasFactory;

    /** @var array<string> */
    protected $fillable = [ // phpcs:ignore
        'name',
        'description',
    ];

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'shift_type');
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }
}
