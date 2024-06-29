<?php

declare(strict_types=1);

namespace Engelsystem\Models\Shifts;

use Carbon\Carbon;
use Engelsystem\Models\BaseModel;
use Engelsystem\Models\Location;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @property int                                          $id
 * @property string                                       $name
 * @property string                                       $url
 * @property int                                          $shift_type
 * @property bool                                         $needed_from_shift_type
 * @property int                                          $minutes_before
 * @property int                                          $minutes_after
 * @property Carbon|null                                  $created_at
 * @property Carbon|null                                  $updated_at
 *
 * @property-read QueryBuilder|Location[]                 $activeLocations
 * @property-read QueryBuilder|Collection|Shift[]         $shifts
 * @property-read QueryBuilder|Collection|ScheduleShift[] $scheduleShifts
 * @property-read QueryBuilder|ShiftType                  $shiftType
 *
 * @method static QueryBuilder|Schedule[] whereId($value)
 * @method static QueryBuilder|Schedule[] whereName($value)
 * @method static QueryBuilder|Schedule[] whereUrl($value)
 * @method static QueryBuilder|Schedule[] whereShiftType($value)
 * @method static QueryBuilder|Schedule[] whereNeededFromShiftType($value)
 * @method static QueryBuilder|Schedule[] whereMinutesBefore($value)
 * @method static QueryBuilder|Schedule[] whereMinutesAfter($value)
 * @method static QueryBuilder|Schedule[] whereCreatedAt($value)
 * @method static QueryBuilder|Schedule[] whereUpdatedAt($value)
 */
class Schedule extends BaseModel
{
    use HasFactory;

    /** @var bool enable timestamps */
    public $timestamps = true; // phpcs:ignore

    /** @var array Default attributes */
    protected $attributes = [ // phpcs:ignore
        'needed_from_shift_type' => false,
    ];

    /** @var array<string> */
    protected $casts = [ // phpcs:ignore
        'shift_type'     => 'integer',
        'needed_from_shift_type' => 'boolean',
        'minutes_before' => 'integer',
        'minutes_after'  => 'integer',
    ];

    /** @var array<string> Values that are mass assignable */
    protected $fillable = [ // phpcs:ignore
        'name',
        'url',
        'shift_type',
        'needed_from_shift_type',
        'minutes_before',
        'minutes_after',
    ];

    public function activeLocations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'schedule_locations');
    }

    public function scheduleShifts(): HasMany
    {
        return $this->hasMany(ScheduleShift::class);
    }

    public function shifts(): HasManyThrough
    {
        return $this->hasManyThrough(Shift::class, ScheduleShift::class, 'schedule_id', 'id');
    }

    public function shiftType(): BelongsTo
    {
        return $this->belongsTo(ShiftType::class, 'shift_type', 'id');
    }
}
