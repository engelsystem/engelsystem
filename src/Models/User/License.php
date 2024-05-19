<?php

declare(strict_types=1);

namespace Engelsystem\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @property bool $has_car
 * @property bool $drive_forklift
 * @property bool $drive_car
 * @property bool $drive_3_5t
 * @property bool $drive_7_5t
 * @property bool $drive_12t
 * @property bool $drive_confirmed
 * @property bool $ifsg_certificate_light
 * @property bool $ifsg_certificate
 * @property bool $ifsg_confirmed
 *
 * @method static QueryBuilder|License[] whereHasCar($value)
 * @method static QueryBuilder|License[] whereDriveForklift($value)
 * @method static QueryBuilder|License[] whereDriveCar($value)
 * @method static QueryBuilder|License[] whereDrive35T($value)
 * @method static QueryBuilder|License[] whereDrive75T($value)
 * @method static QueryBuilder|License[] whereDrive12T($value)
 * @method static QueryBuilder|License[] whereDriveConfirmed($value)
 * @method static QueryBuilder|License[] whereIfsgCertificateLight($value)
 * @method static QueryBuilder|License[] whereIfsgCertificate($value)
 * @method static QueryBuilder|License[] whereIfsgConfirmed($value)
 */
class License extends HasUserModel
{
    use HasFactory;

    /** @var string The table associated with the model */
    protected $table = 'users_licenses'; // phpcs:ignore

    /** @var array<string, bool> Default attributes */
    protected $attributes = [ // phpcs:ignore
        'has_car' => false,
        'drive_forklift' => false,
        'drive_car' => false,
        'drive_3_5t' => false,
        'drive_7_5t' => false,
        'drive_12t' => false,
        'drive_confirmed' => false,
        'ifsg_certificate_light' => false,
        'ifsg_certificate' => false,
        'ifsg_confirmed' => false,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [ // phpcs:ignore
        'user_id',
        'has_car',
        'drive_forklift',
        'drive_car',
        'drive_3_5t',
        'drive_7_5t',
        'drive_12t',
        'drive_confirmed',
        'ifsg_certificate_light',
        'ifsg_certificate',
        'ifsg_confirmed',
    ];

    /** @var array<string> */
    protected $casts = [ // phpcs:ignore
        'user_id' => 'integer',
        'has_car' => 'boolean',
        'drive_forklift' => 'boolean',
        'drive_car' => 'boolean',
        'drive_3_5t' => 'boolean',
        'drive_7_5t' => 'boolean',
        'drive_12t' => 'boolean',
        'drive_confirmed' => 'boolean',
        'ifsg_certificate_light' => 'boolean',
        'ifsg_certificate' => 'boolean',
        'ifsg_confirmed' => 'boolean',
    ];

    /**
     * If the user wants to drive at the event
     */
    public function wantsToDrive(): bool
    {
        return $this->drive_forklift
            || $this->drive_car
            || $this->drive_3_5t
            || $this->drive_7_5t
            || $this->drive_12t;
    }
}
