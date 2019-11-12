<?php

namespace Engelsystem\Models\User;

use Carbon\Carbon;
use Engelsystem\Models\BaseModel;
use Engelsystem\Models\News;
use Engelsystem\Models\NewsComment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @property integer                        $id
 * @property string                         $name
 * @property string                         $email
 * @property string                         $password
 * @property string                         $api_key
 * @property Carbon|null                    $last_login_at
 * @property Carbon                         $created_at
 * @property Carbon                         $updated_at
 *
 * @property-read QueryBuilder|Contact      $contact
 * @property-read QueryBuilder|PersonalData $personalData
 * @property-read QueryBuilder|Settings     $settings
 * @property-read QueryBuilder|State        $state
 * @property-read Collection|NewsComment[]  $newsComments
 *
 * @method static QueryBuilder|User whereId($value)
 * @method static QueryBuilder|User[] whereName($value)
 * @method static QueryBuilder|User[] whereEmail($value)
 * @method static QueryBuilder|User[] wherePassword($value)
 * @method static QueryBuilder|User[] whereApiKey($value)
 * @method static QueryBuilder|User[] whereLastLoginAt($value)
 * @method static QueryBuilder|User[] whereCreatedAt($value)
 * @method static QueryBuilder|User[] whereUpdatedAt($value)
 */
class User extends BaseModel
{
    /** @var bool enable timestamps */
    public $timestamps = true;

    /** The attributes that are mass assignable */
    protected $fillable = [
        'name',
        'password',
        'email',
        'api_key',
        'last_login_at',
    ];

    /** @var array The attributes that should be hidden for serialization */
    protected $hidden = [
        'api_key',
        'password',
    ];

    /** @var array The attributes that should be mutated to dates */
    protected $dates = [
        'last_login_at',
    ];

    /**
     * @return HasOne
     */
    public function contact()
    {
        return $this
            ->hasOne(Contact::class)
            ->withDefault();
    }

    /**
     * @return HasOne
     */
    public function personalData()
    {
        return $this
            ->hasOne(PersonalData::class)
            ->withDefault();
    }

    /**
     * @return HasOne
     */
    public function settings()
    {
        return $this
            ->hasOne(Settings::class)
            ->withDefault();
    }

    /**
     * @return HasOne
     */
    public function state()
    {
        return $this
            ->hasOne(State::class)
            ->withDefault();
    }

    /**
     * @return HasMany
     */
    public function news(): HasMany
    {
        return $this->hasMany(News::class);
    }

    /**
     * @return HasMany
     */
    public function newsComments(): HasMany
    {
        return $this->hasMany(NewsComment::class);
    }
}
