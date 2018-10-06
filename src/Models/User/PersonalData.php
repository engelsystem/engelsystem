<?php

namespace Engelsystem\Models\User;

/**
 * @property string         $first_name
 * @property string         $last_name
 * @property string         $shirt_size
 * @property \Carbon\Carbon $arrival_date
 * @property \Carbon\Carbon $planned_arrival_date
 * @property \Carbon\Carbon $planned_departure_date
 *
 * @method static \Illuminate\Database\Query\Builder|\Engelsystem\Models\User\PersonalData whereFirstName($value)
 * @method static \Illuminate\Database\Query\Builder|\Engelsystem\Models\User\PersonalData whereLastName($value)
 * @method static \Illuminate\Database\Query\Builder|\Engelsystem\Models\User\PersonalData whereShirtSize($value)
 * @method static \Illuminate\Database\Query\Builder|\Engelsystem\Models\User\PersonalData whereArrivalDate($value)
 * @method static \Illuminate\Database\Query\Builder|\Engelsystem\Models\User\PersonalData wherePlannedArrivalDate($value)
 * @method static \Illuminate\Database\Query\Builder|\Engelsystem\Models\User\PersonalData wherePlannedDepartureDate($value)
 */
class PersonalData extends HasUserModel
{
    /** @var string The table associated with the model */
    protected $table = 'users_personal_data';

    /** @var array The attributes that should be mutated to dates */
    protected $dates = [
        'arrival_date',
        'planned_arrival_date',
        'planned_departure_date',
    ];

    /** The attributes that are mass assignable. */
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'shirt_size',
        'arrival_date',
        'planned_arrival_date',
        'planned_departure_date',
    ];
}
