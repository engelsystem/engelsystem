<?php

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
    protected $table = 'users_personal_data';

    /** @var array The attributes that should be mutated to dates */
    protected $dates = [
        'planned_arrival_date',
        'planned_departure_date',
    ];

    /** The attributes that are mass assignable. */
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'pronoun',
        'shirt_size',
        'planned_arrival_date',
        'planned_departure_date',
    ];
}
