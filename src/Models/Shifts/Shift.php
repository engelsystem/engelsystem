<?php

declare(strict_types=1);

namespace Engelsystem\Models\Shifts;

use Carbon\Carbon;
use Engelsystem\Models\BaseModel;
use Engelsystem\Models\Room;
use Engelsystem\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @property int                         $id
 * @property string                      $title
 * @property string                      $description
 * @property string                      $url
 * @property Carbon                      $start
 * @property Carbon                      $end
 * @property int                         $shift_type_id
 * @property int                         $room_id
 * @property string                      $transaction_id
 * @property int                         $created_by
 * @property int|null                    $updated_by
 * @property Carbon|null                 $created_at
 * @property Carbon|null                 $updated_at
 *
 * @property-read QueryBuilder|Schedule  $schedule
 * @property-read QueryBuilder|ShiftType $shiftType
 * @property-read QueryBuilder|Room      $room
 * @property-read QueryBuilder|User      $createdBy
 * @property-read QueryBuilder|User|null $updatedBy
 *
 * @method static QueryBuilder|Shift[] whereId($value)
 * @method static QueryBuilder|Shift[] whereTitle($value)
 * @method static QueryBuilder|Shift[] whereDescription($value)
 * @method static QueryBuilder|Shift[] whereUrl($value)
 * @method static QueryBuilder|Shift[] whereStart($value)
 * @method static QueryBuilder|Shift[] whereEnd($value)
 * @method static QueryBuilder|Shift[] whereShiftTypeId($value)
 * @method static QueryBuilder|Shift[] whereRoomId($value)
 * @method static QueryBuilder|Shift[] whereTransactionId($value)
 * @method static QueryBuilder|Shift[] whereCreatedBy($value)
 * @method static QueryBuilder|Shift[] whereUpdatedBy($value)
 * @method static QueryBuilder|Shift[] whereCreatedAt($value)
 * @method static QueryBuilder|Shift[] whereUpdatedAt($value)
 */
class Shift extends BaseModel
{
    use HasFactory;

    /** @var bool enable timestamps */
    public $timestamps = true; // phpcs:ignore

    /** @var array<string, string> */
    protected $casts = [ // phpcs:ignore
        'shift_type_id' => 'integer',
        'room_id'       => 'integer',
        'created_by'    => 'integer',
        'updated_by'    => 'integer',
    ];

    /** @var array<string> Values that are mass assignable */
    protected $fillable = [ // phpcs:ignore
        'title',
        'description',
        'url',
        'start',
        'end',
        'shift_type_id',
        'room_id',
        'transaction_id',
        'created_by',
        'updated_by',
    ];

    /** @var array<string> Values that are DateTimes */
    protected $dates = [ // phpcs:ignore
        'start',
        'end',
    ];

    public function schedule(): HasOneThrough
    {
        return $this->hasOneThrough(Schedule::class, ScheduleShift::class, null, 'id', null, 'schedule_id');
    }

    public function shiftType(): BelongsTo
    {
        return $this->belongsTo(ShiftType::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
