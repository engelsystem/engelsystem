<?php

declare(strict_types=1);

namespace Engelsystem\Models\Shifts;

use Engelsystem\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @property int                        $shift_id
 * @property int                        $schedule_id
 * @property string                     $guid
 *
 * @property-read QueryBuilder|Schedule $schedule
 * @property-read QueryBuilder|Shift    $shift
 *
 * @method static QueryBuilder|ScheduleShift[] whereShiftId($value)
 * @method static QueryBuilder|ScheduleShift[] whereScheduleId($value)
 * @method static QueryBuilder|ScheduleShift[] whereGuid($value)
 */
class ScheduleShift extends BaseModel
{
    use HasFactory;

    /** @var string The primary key for the model */
    protected $primaryKey = 'shift_id'; // phpcs:ignore

    /** @var string Required because it is not schedule_shifts */
    protected $table = 'schedule_shift'; // phpcs:ignore

    /** @var array<string> Values that are mass assignable */
    protected $fillable = ['shift_id', 'schedule_id', 'guid']; // phpcs:ignore

    /** @var array<string, string> */
    protected $casts = [ // phpcs:ignore
        'shift_id'    => 'integer',
        'schedule_id' => 'integer',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }
}
