<?php

declare(strict_types=1);

namespace Engelsystem\Models\Shifts;

use Carbon\Carbon;
use Engelsystem\Models\BaseModel;
use Engelsystem\Models\Location;
use Engelsystem\Models\User\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @property int                               $id
 * @property string                            $title
 * @property string                            $description
 * @property string                            $url
 * @property Carbon                            $start
 * @property Carbon                            $end
 * @property int                               $shift_type_id
 * @property int                               $location_id
 * @property string|null                       $transaction_id
 * @property int                               $created_by
 * @property int|null                          $updated_by
 * @property Carbon|null                       $created_at
 * @property Carbon|null                       $updated_at
 *
 * @property-read Collection|NeededAngelType[] $neededAngelTypes
 * @property-read Schedule                     $schedule
 * @property-read Collection|ShiftEntry[]      $shiftEntries
 * @property-read ShiftType                    $shiftType
 * @property-read Location                     $location
 * @property-read User                         $createdBy
 * @property-read User|null                    $updatedBy
 *
 * @method static QueryBuilder|Shift[] whereId($value)
 * @method static QueryBuilder|Shift[] whereTitle($value)
 * @method static QueryBuilder|Shift[] whereDescription($value)
 * @method static QueryBuilder|Shift[] whereUrl($value)
 * @method static QueryBuilder|Shift[] whereStart($value)
 * @method static QueryBuilder|Shift[] whereEnd($value)
 * @method static QueryBuilder|Shift[] whereShiftTypeId($value)
 * @method static QueryBuilder|Shift[] whereLocationId($value)
 * @method static QueryBuilder|Shift[] whereTransactionId($value)
 * @method static QueryBuilder|Shift[] whereCreatedBy($value)
 * @method static QueryBuilder|Shift[] whereUpdatedBy($value)
 * @method static QueryBuilder|Shift[] whereCreatedAt($value)
 * @method static QueryBuilder|Shift[] whereUpdatedAt($value)
 */
class Shift extends BaseModel
{
    use HasFactory;

    /** @var array<string, string|null> default attributes */
    protected $attributes = [ // phpcs:ignore
        'description'    => '',
        'url'            => '',
        'transaction_id' => null,
        'updated_by'     => null,
    ];

    /** @var bool enable timestamps */
    public $timestamps = true; // phpcs:ignore

    /** @var array<string, string> */
    protected $casts = [ // phpcs:ignore
        'shift_type_id' => 'integer',
        'location_id'   => 'integer',
        'created_by'    => 'integer',
        'updated_by'    => 'integer',
        'start'         => 'datetime',
        'end'           => 'datetime',
    ];

    /** @var array<string> Values that are mass assignable */
    protected $fillable = [ // phpcs:ignore
        'title',
        'description',
        'url',
        'start',
        'end',
        'shift_type_id',
        'location_id',
        'transaction_id',
        'created_by',
        'updated_by',
    ];

    public function neededAngelTypes(): HasMany
    {
        return $this->hasMany(NeededAngelType::class);
    }

    public function schedule(): HasOneThrough
    {
        return $this->hasOneThrough(Schedule::class, ScheduleShift::class, null, 'id', null, 'schedule_id');
    }

    public function shiftEntries(): HasMany
    {
        return $this->hasMany(ShiftEntry::class);
    }

    public function shiftType(): BelongsTo
    {
        return $this->belongsTo(ShiftType::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Check if the shift is a night shift
     */
    public function isNightShift(): bool
    {
        $config = config('night_shifts');

        /** @see User_get_shifts_sum_query to keep it in sync */
        return $config['enabled'] && (
                // Starts during night
                $this->start->hour >= $config['start'] && $this->start->hour < $config['end']
                // Ends during night
                || (
                    $this->end->hour > $config['start']
                    || $this->end->hour == $config['start'] && $this->end->minute > 0
                ) && $this->end->hour <= $config['end']
                // Starts before and ends after night
                || $this->start->hour <= $config['start'] && $this->end->hour >= $config['end']
            );
    }

    /**
     * Calculate the shifts night multiplier
     */
    public function getNightShiftMultiplier(): float
    {
        if (!$this->isNightShift()) {
            return 1;
        }

        return config('night_shifts')['multiplier'];
    }
}
