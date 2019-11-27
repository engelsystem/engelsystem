<?php

namespace Engelsystem\Models\Shifts;

use Engelsystem\Models\BaseModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @property int                                          $id
 * @property string                                       $url
 *
 * @property-read QueryBuilder|Collection|ScheduleShift[] $scheduleShifts
 *
 * @method static QueryBuilder|Schedule[] whereId($value)
 * @method static QueryBuilder|Schedule[] whereUrl($value)
 */
class Schedule extends BaseModel
{
    /** @var array Values that are mass assignable */
    protected $fillable = ['url'];

    /**
     * @return HasMany
     */
    public function scheduleShifts()
    {
        return $this->hasMany(ScheduleShift::class);
    }
}
