<?php

namespace Engelsystem\Models\Shifts;

use Carbon\Carbon;
use Engelsystem\Models\BaseModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @property int                                          $id
 * @property string                                       $name
 * @property string                                       $url
 * @property int                                          $shift_type
 * @property int                                          $minutes_before
 * @property int                                          $minutes_after
 * @property Carbon                                       $created_at
 * @property Carbon                                       $updated_at
 *
 * @property-read QueryBuilder|Collection|ScheduleShift[] $scheduleShifts
 *
 * @method static QueryBuilder|Schedule[] whereId($value)
 * @method static QueryBuilder|Schedule[] whereName($value)
 * @method static QueryBuilder|Schedule[] whereUrl($value)
 * @method static QueryBuilder|Schedule[] whereShiftType($value)
 * @method static QueryBuilder|Schedule[] whereMinutesBefore($value)
 * @method static QueryBuilder|Schedule[] whereMinutesAfter($value)
 * @method static QueryBuilder|Schedule[] whereCreatedAt($value)
 * @method static QueryBuilder|Schedule[] whereUpdatedAt($value)
 */
class Schedule extends BaseModel
{
    use HasFactory;

    /** @var bool enable timestamps */
    public $timestamps = true;

    /** @var array Values that are mass assignable */
    protected $fillable = [
        'name',
        'url',
        'shift_type',
        'minutes_before',
        'minutes_after',
    ];

    /**
     * @return HasMany
     */
    public function scheduleShifts()
    {
        return $this->hasMany(ScheduleShift::class);
    }
}
