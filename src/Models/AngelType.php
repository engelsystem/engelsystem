<?php

declare(strict_types=1);

namespace Engelsystem\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @property int     $id
 * @property string  $name
 * @property string  $description
 * @property string  $contact_name
 * @property string  $contact_dect
 * @property string  $contact_email
 * @property boolean $restricted # If users need an introduction
 * @property boolean $requires_driver_license # If users must have a driver license
 * @property boolean $no_self_signup # Users can sign up for shifts
 * @property boolean $show_on_dashboard # Show on public dashboard
 * @property boolean $hide_register # Hide from registration page
 *
 * @method static QueryBuilder|AngelType[] whereId($value)
 * @method static QueryBuilder|AngelType[] whereName($value)
 * @method static QueryBuilder|AngelType[] whereDescription($value)
 * @method static QueryBuilder|AngelType[] whereContactName($value)
 * @method static QueryBuilder|AngelType[] whereContactDect($value)
 * @method static QueryBuilder|AngelType[] whereContactEmail($value)
 * @method static QueryBuilder|AngelType[] whereRestricted($value)
 * @method static QueryBuilder|AngelType[] whereRequiresDriverLicense($value)
 * @method static QueryBuilder|AngelType[] whereNoSelfSignup($value)
 * @method static QueryBuilder|AngelType[] whereShowOnDashboard($value)
 * @method static QueryBuilder|AngelType[] whereHideRegister($value)
 */
class AngelType extends BaseModel
{
    use HasFactory;

    /** @var string[] */
    protected $fillable = [
        'name',
        'description',

        'contact_name',
        'contact_dect',
        'contact_email',

        'restricted',
        'requires_driver_license',
        'no_self_signup',
        'show_on_dashboard',
        'hide_register',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'restricted'              => 'boolean',
        'requires_driver_license' => 'boolean',
        'no_self_signup'          => 'boolean',
        'show_on_dashboard'       => 'boolean',
        'hide_register'           => 'boolean',
    ];

    /**
     * @codeCoverageIgnore For some reasons parent::boot gets ignored here 0o
     */
    protected static function boot(): void
    {
        parent::boot();
        static::addGlobalScope('order', fn(Builder $builder) => $builder->orderBy('name'));
    }

    public function hasContactInfo(): bool
    {
        return !empty($this->contact_name)
            || !empty($this->contact_dect)
            || !empty($this->contact_email);
    }
}
