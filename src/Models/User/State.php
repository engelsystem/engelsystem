<?php

declare(strict_types=1);

namespace Engelsystem\Models\User;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @property bool        $arrived
 * @property Carbon|null $arrival_date
 * @property string|null $user_info
 * @property bool        $active
 * @property bool        $force_active
 * @property bool        $got_goodie
 * @property int         $got_voucher
 *
 * @method static QueryBuilder|State[] whereArrived($value)
 * @method static QueryBuilder|State[] whereArrivalDate($value)
 * @method static QueryBuilder|State[] whereUserInfo($value)
 * @method static QueryBuilder|State[] whereActive($value)
 * @method static QueryBuilder|State[] whereForceActive($value)
 * @method static QueryBuilder|State[] whereGotGoodie($value)
 * @method static QueryBuilder|State[] whereGotVoucher($value)
 */
class State extends HasUserModel
{
    use HasFactory;

    /** @var string The table associated with the model */
    protected $table = 'users_state'; // phpcs:ignore

    /** @var array<string, bool|int|null> Default attributes */
    protected $attributes = [ // phpcs:ignore
        'arrived'      => false,
        'arrival_date' => null,
        'user_info'    => null,
        'active'       => false,
        'force_active' => false,
        'got_goodie'   => false,
        'got_voucher'  => 0,
    ];

    /** @var array<string, string> */
    protected $casts = [ // phpcs:ignore
        'user_id'      => 'integer',
        'arrived'      => 'boolean',
        'arrival_date' => 'datetime',
        'active'       => 'boolean',
        'force_active' => 'boolean',
        'got_goodie'   => 'boolean',
        'got_voucher'  => 'integer',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [ // phpcs:ignore
        'user_id',
        'arrived',
        'arrival_date',
        'user_info',
        'active',
        'force_active',
        'got_goodie',
        'got_voucher',
    ];
}
