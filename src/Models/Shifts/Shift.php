<?php

declare(strict_types=1);

namespace Engelsystem\Models\Shifts;

use Carbon\Carbon;
use Engelsystem\Models\BaseModel;
use Engelsystem\Models\Location;
use Engelsystem\Models\Tag;
use Engelsystem\Models\User\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\Grammars\SQLiteGrammar;
use Illuminate\Database\Query\JoinClause;

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
 * @property bool                              $requires_supervisor_for_minors
 * @property string|null                       $minor_supervision_notes
 * @property Carbon|null                       $created_at
 * @property Carbon|null                       $updated_at
 *
 * @property-read Collection|NeededAngelType[] $neededAngelTypes
 * @property-read Schedule                     $schedule
 * @property-read ScheduleShift                $scheduleShift
 * @property-read Collection|ShiftEntry[]      $shiftEntries
 * @property-read ShiftType                    $shiftType
 * @property-read Collection|Tag[]             $tags
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

    /** @var array<string, string|null|bool> default attributes */
    protected $attributes = [ // phpcs:ignore
        'description'                   => '',
        'url'                           => '',
        'transaction_id'                => null,
        'updated_by'                    => null,
        'requires_supervisor_for_minors' => false,
        'minor_supervision_notes'       => null,
    ];

    /** @var bool enable timestamps */
    public $timestamps = true; // phpcs:ignore

    /** @var array<string, string> */
    protected $casts = [ // phpcs:ignore
        'shift_type_id' => 'integer',
        'location_id'   => 'integer',
        'created_by'    => 'integer',
        'updated_by'    => 'integer',
        'start'                          => 'datetime',
        'end'                            => 'datetime',
        'requires_supervisor_for_minors' => 'boolean',
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
        'requires_supervisor_for_minors',
        'minor_supervision_notes',
    ];

    public function neededAngelTypes(): HasMany
    {
        return $this->hasMany(NeededAngelType::class);
    }

    public function schedule(): HasOneThrough
    {
        return $this->hasOneThrough(Schedule::class, ScheduleShift::class, null, 'id', null, 'schedule_id');
    }

    public function scheduleShift(): HasOne
    {
        return $this->hasOne(ScheduleShift::class);
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

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'shift_tags');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeNeedsUsers(Builder $query): void
    {
        $query
            ->addSelect([
                // This is "hidden" behind an attribute to not "poison" the SELECT default with fields from added joins
                'needs_users' => Shift::from('shifts as s2')
                    ->leftJoin('schedule_shift', 'schedule_shift.shift_id', 's2.id')
                    ->leftJoin('schedules', 'schedules.id', 'schedule_shift.schedule_id')
                    ->leftJoin('needed_angel_types', function (JoinClause $join): void {
                        // Directly
                        $join->on('needed_angel_types.shift_id', 's2.id')
                            // Via schedule location
                            ->orOn('needed_angel_types.location_id', 's2.location_id')
                            // Via schedule shift type
                            ->orOn('needed_angel_types.shift_type_id', 'schedules.shift_type');
                    })
                    ->whereColumn('s2.id', 'shifts.id')
                    ->where(function (Builder $query): void {
                        $query
                            ->where(function (Builder $query): void {
                                $query
                                    // Direct requirement
                                    ->whereColumn('needed_angel_types.shift_id', 's2.id')
                                    // Or has schedule & via location
                                    ->orWhere(function (Builder $query): void {
                                        $query
                                            ->where('schedules.needed_from_shift_type', false)
                                            ->whereColumn('needed_angel_types.location_id', 's2.location_id');
                                    })
                                    // Or has schedule & via type
                                    ->orWhere(function (Builder $query): void {
                                        $query
                                            ->where('schedules.needed_from_shift_type', true)
                                            ->whereColumn('needed_angel_types.shift_type_id', 's2.shift_type_id');
                                    });
                            });
                    })
                    ->selectRaw('COUNT(*) > 0'),
            ]);

        if ($query->getConnection()->getQueryGrammar() instanceof SQLiteGrammar) {
            // SQLite does not support HAVING for non-aggregate queries
            $query->where('needs_users', '>', 0);
        } else {
            // @codeCoverageIgnoreStart
            // needs_users is defined on select and thus only available after select
            $query->having('needs_users', '>', 0);
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * get next shift with same shift type and location
     */
    public function nextShift(): Shift|null
    {
        $query = Shift::query();
        if (Shift::whereTitle($this->title)->where('start', '>', $this->start)->exists()) {
            $query = $query->where('title', $this->title);
        }
        return $query
            ->where('shift_type_id', $this->shiftType->id)
            ->where('location_id', $this->location->id)
            ->where('start', '>', $this->start)
            ->orderBy('start')
            ->first();
    }

    /**
     * get previous shift with same shift type and location
     */
    public function previousShift(): Shift|null
    {
        $query = Shift::query();
        if (Shift::whereTitle($this->title)->where('end', '<', $this->end)->exists()) {
            $query = $query->where('title', $this->title);
        }
        return $query
            ->where('shift_type_id', $this->shiftType->id)
            ->where('location_id', $this->location->id)
            ->where('end', '<', $this->end)
            ->orderBy('end', 'desc')
            ->first();
    }

    /**
     * Check if the shift is a night shift
     */
    public function isNightShift(): bool
    {
        $config = config('night_shifts');

        /** @see \Engelsystem\Helpers\Goodie::shiftScoreQuery to keep them in sync */
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
