<?php

declare(strict_types=1);

namespace Engelsystem\Models;

use Illuminate\Database\Eloquent\Builder;
use Engelsystem\Models\User\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @property int                    $id
 * @property string                 $name
 * @property string                 $description
 * @property string                 $contact_name
 * @property string                 $contact_dect
 * @property string                 $contact_email
 * @property boolean                $restricted # If users need an introduction
 * @property boolean                $requires_driver_license # If users must have a driver license
 * @property boolean                $no_self_signup # Users can sign up for shifts
 * @property boolean                $show_on_dashboard # Show on public dashboard
 * @property boolean                $hide_register # Hide from registration page
 *
 * @property-read Collection|User[] $userAngelTypes
 * @property-read UserAngelType     $pivot
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

    /** @var array<string> */
    protected $fillable = [ // phpcs:ignore
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
    protected $casts = [ // phpcs:ignore
        'restricted'              => 'boolean',
        'requires_driver_license' => 'boolean',
        'no_self_signup'          => 'boolean',
        'show_on_dashboard'       => 'boolean',
        'hide_register'           => 'boolean',
    ];

    public function userAngelTypes(): BelongsToMany
    {
        return $this
            ->belongsToMany(User::class, 'user_angel_type')
            ->using(UserAngelType::class)
            ->withPivot(UserAngelType::getPivotAttributes());
    }

    public function hasContactInfo(): bool
    {
        return !empty($this->contact_name)
            || !empty($this->contact_dect)
            || !empty($this->contact_email);
    }

    /**
     * @codeCoverageIgnore For some reasons parent::boot get s ignored here 0o
     */
    protected static function boot(): void
    {
        parent::boot();
        static::addGlobalScope('order', fn(Builder $builder) => $builder->orderBy('name'));
    }
}
