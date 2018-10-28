<?php

namespace Engelsystem\Models\User;

/**
 * @property bool                $arrived
 * @property \Carbon\Carbon|null $arrival_date
 * @property bool                $active
 * @property bool                $force_active
 * @property bool                $got_shirt
 * @property int                 $got_voucher
 *
 * @method static \Illuminate\Database\Query\Builder|\Engelsystem\Models\User\State[] whereArrived($value)
 * @method static \Illuminate\Database\Query\Builder|\Engelsystem\Models\User\State[] whereArrivalDate($value)
 * @method static \Illuminate\Database\Query\Builder|\Engelsystem\Models\User\State[] whereActive($value)
 * @method static \Illuminate\Database\Query\Builder|\Engelsystem\Models\User\State[] whereForceActive($value)
 * @method static \Illuminate\Database\Query\Builder|\Engelsystem\Models\User\State[] whereGotShirt($value)
 * @method static \Illuminate\Database\Query\Builder|\Engelsystem\Models\User\State[] whereGotVoucher($value)
 */
class State extends HasUserModel
{
    /** @var string The table associated with the model */
    protected $table = 'users_state';

    /** @var array The attributes that should be mutated to dates */
    protected $dates = [
        'arrival_date',
    ];

    /** The attributes that are mass assignable */
    protected $fillable = [
        'user_id',
        'arrived',
        'arrival_date',
        'active',
        'force_active',
        'got_shirt',
        'got_voucher',
    ];
}
