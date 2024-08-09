<?php

declare(strict_types=1);

namespace Engelsystem\Models;

use Carbon\Carbon;
use Engelsystem\Models\Shifts\NeededAngelType;
use Engelsystem\Models\Shifts\Schedule;
use Engelsystem\Models\Shifts\Shift;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @property int                               $id
 * @property string                            $name
 * @property string|null                       $map_url
 * @property string|null                       $description
 * @property string|null                       $dect
 * @property Carbon|null                       $created_at
 * @property Carbon|null                       $updated_at
 *
 * @property-read Collection|Schedule[]        $activeForSchedules
 * @property-read Collection|NeededAngelType[] $neededAngelTypes
 * @property-read Collection|Shift[]           $shifts
 *
 * @method static QueryBuilder|Location[] whereId($value)
 * @method static QueryBuilder|Location[] whereName($value)
 * @method static QueryBuilder|Location[] whereMapUrl($value)
 * @method static QueryBuilder|Location[] whereDect($value)
 * @method static QueryBuilder|Location[] whereDescription($value)
 * @method static QueryBuilder|Location[] whereCreatedAt($value)
 * @method static QueryBuilder|Location[] whereUpdatedAt($value)
 */
class Location extends BaseModel
{
    use HasFactory;

    /** @var bool Enable timestamps */
    public $timestamps = true; // phpcs:ignore

    /** @var array<string, null> default attributes */
    protected $attributes = [ // phpcs:ignore
        'map_url'     => null,
        'description' => null,
        'dect'        => null,
    ];

    /** @var array<string> */
    protected $fillable = [ // phpcs:ignore
        'name',
        'dect',
        'map_url',
        'description',
    ];

    public function activeForSchedules(): BelongsToMany
    {
        return $this->belongsToMany(Schedule::class, 'schedule_locations');
    }

    public function neededAngelTypes(): HasMany
    {
        return $this->hasMany(NeededAngelType::class);
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }
}
