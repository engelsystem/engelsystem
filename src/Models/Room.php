<?php

declare(strict_types=1);

namespace Engelsystem\Models;

use Carbon\Carbon;
use Engelsystem\Models\Shifts\NeededAngelType;
use Engelsystem\Models\Shifts\Shift;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @property int                               $id
 * @property string                            $name
 * @property string                            $map_url
 * @property string                            $description
 * @property string                            $dect
 * @property Carbon|null                       $created_at
 * @property Carbon|null                       $updated_at
 *
 * @property-read Collection|NeededAngelType[] $neededAngelTypes
 * @property-read Collection|Shift[]           $shifts
 *
 * @method static QueryBuilder|Room[] whereId($value)
 * @method static QueryBuilder|Room[] whereName($value)
 * @method static QueryBuilder|Room[] whereMapUrl($value)
 * @method static QueryBuilder|Room[] whereDect($value)
 * @method static QueryBuilder|Room[] whereDescription($value)
 * @method static QueryBuilder|Room[] whereCreatedAt($value)
 * @method static QueryBuilder|Room[] whereUpdatedAt($value)
 */
class Room extends BaseModel
{
    use HasFactory;

    /** @var bool Enable timestamps */
    public $timestamps = true; // phpcs:ignore

    /** @var array<string> */
    protected $fillable = [ // phpcs:ignore
        'name',
        'dect',
        'map_url',
        'description',
    ];

    public function neededAngelTypes(): HasMany
    {
        return $this->hasMany(NeededAngelType::class);
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }
}
