<?php

declare(strict_types=1);

namespace Engelsystem\Models\User;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $pronoun
 * @property string|null $shirt_size
 * @property Carbon|null $planned_arrival_date
 * @property Carbon|null $planned_departure_date
 *
 * @method static QueryBuilder|PersonalData[] whereFirstName($value)
 * @method static QueryBuilder|PersonalData[] whereLastName($value)
 * @method static QueryBuilder|PersonalData[] wherePronoun($value)
 * @method static QueryBuilder|PersonalData[] whereShirtSize($value)
 * @method static QueryBuilder|PersonalData[] wherePlannedArrivalDate($value)
 * @method static QueryBuilder|PersonalData[] wherePlannedDepartureDate($value)
 */
class PersonalData extends HasUserModel
{
    use HasFactory;

    /** @var string The table associated with the model */
    protected $table = 'users_personal_data'; // phpcs:ignore

    /** @var array<string, null> default attributes */
    protected $attributes = [ // phpcs:ignore
        'first_name'             => null,
        'last_name'              => null,
        'pronoun'                => null,
        'shirt_size'             => null,
        'planned_arrival_date'   => null,
        'planned_departure_date' => null,
    ];

    /** @var array<string, string> */
    protected $casts = [ // phpcs:ignore
        'user_id'                => 'integer',
        'planned_arrival_date'   => 'datetime',
        'planned_departure_date' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [ // phpcs:ignore
        'user_id',
        'first_name',
        'last_name',
        'pronoun',
        'shirt_size',
        'planned_arrival_date',
        'planned_departure_date',
    ];
}
