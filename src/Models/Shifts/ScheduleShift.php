<?php

namespace Engelsystem\Models\Shifts;

use Engelsystem\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @property int                        $shift_id
 * @property int                        $schedule_id
 * @property string                     $guid
 *
 * @property-read QueryBuilder|Schedule $schedule
 *
 * @method static QueryBuilder|ScheduleShift[] whereShiftId($value)
 * @method static QueryBuilder|ScheduleShift[] whereScheduleId($value)
 * @method static QueryBuilder|ScheduleShift[] whereGuid($value)
 */
class ScheduleShift extends BaseModel
{
    /** @var string The primary key for the model */
    protected $primaryKey = 'shift_id';

    /** @var string Required because it is not schedule_shifts */
    protected $table = 'schedule_shift';

    /** @var array Values that are mass assignable */
    protected $fillable = ['shift_id', 'schedule_id', 'guid'];

    /** @var array */
    protected $casts = [
        'shift_id'    => 'integer',
        'schedule_id' => 'integer',
    ];

    /**
     * @return BelongsTo
     */
    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }
}
