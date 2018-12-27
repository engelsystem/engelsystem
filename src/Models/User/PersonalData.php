<?php

namespace Engelsystem\Models\User;

/**
 * @property string|null         $first_name
 * @property string|null         $last_name
 * @property string|null         $shirt_size
 * @property \Carbon\Carbon|null $planned_arrival_date
 * @property \Carbon\Carbon|null $planned_departure_date
 *
 * @method static \Illuminate\Database\Query\Builder|\Engelsystem\Models\User\PersonalData[] whereFirstName($value)
 * @method static \Illuminate\Database\Query\Builder|\Engelsystem\Models\User\PersonalData[] whereLastName($value)
 * @method static \Illuminate\Database\Query\Builder|\Engelsystem\Models\User\PersonalData[] whereShirtSize($value)
 * @method static \Illuminate\Database\Query\Builder|\Engelsystem\Models\User\PersonalData[] wherePlannedArrivalDate($value)
 * @method static \Illuminate\Database\Query\Builder|\Engelsystem\Models\User\PersonalData[] wherePlannedDepartureDate($value)
 */
class PersonalData extends HasUserModel
{
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
        'shirt_size',
        'planned_arrival_date',
        'planned_departure_date',
    ];
}
