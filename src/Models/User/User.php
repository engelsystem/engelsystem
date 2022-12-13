<?php

namespace Engelsystem\Models\User;

use Carbon\Carbon;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\BaseModel;
use Engelsystem\Models\Group;
use Engelsystem\Models\Message;
use Engelsystem\Models\News;
use Engelsystem\Models\NewsComment;
use Engelsystem\Models\OAuth;
use Engelsystem\Models\Privilege;
use Engelsystem\Models\Question;
use Engelsystem\Models\UserAngelType;
use Engelsystem\Models\Worklog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection as SupportCollection;

/**
 * @property int                                $id
 * @property string                             $name
 * @property string                             $email
 * @property string                             $password
 * @property string                             $api_key
 * @property Carbon|null                        $last_login_at
 * @property Carbon                             $created_at
 * @property Carbon                             $updated_at
 *
 * @property-read QueryBuilder|Contact          $contact
 * @property-read QueryBuilder|License          $license
 * @property-read QueryBuilder|PersonalData     $personalData
 * @property-read QueryBuilder|Settings         $settings
 * @property-read QueryBuilder|State            $state
 *
 * @property-read Collection|Group[]            $groups
 * @property-read Collection|News[]             $news
 * @property-read Collection|NewsComment[]      $newsComments
 * @property-read Collection|OAuth[]            $oauth
 * @property-read SupportCollection|Privilege[] $privileges
 * @property-read Collection|AngelType[]        $userAngelTypes
 * @property-read UserAngelType                 $pivot
 * @property-read Collection|Worklog[]          $worklogs
 * @property-read Collection|Worklog[]          $worklogsCreated
 * @property-read Collection|Question[]         $questionsAsked
 * @property-read Collection|Question[]         $questionsAnswered
 * @property-read Collection|Message[]          $messagesReceived
 * @property-read Collection|Message[]          $messagesSent
 * @property-read Collection|Message[]          $messages
 *
 * @method static QueryBuilder|User[] whereId($value)
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
    use HasFactory;

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
     * @return BelongsToMany
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'users_groups');
    }

    /**
     * @return HasOne
     */
    public function license()
    {
        return $this
            ->hasOne(License::class)
            ->withDefault();
    }

    /**
     * @return Builder
     */
    public function privileges(): Builder
    {
        /** @var Builder $builder */
        $builder = Privilege::query()
            ->whereIn('id', function ($query) {
                /** @var QueryBuilder $query */
                $query->select('privilege_id')
                    ->from('group_privileges')
                    ->join('users_groups', 'users_groups.group_id', '=', 'group_privileges.group_id')
                    ->where('users_groups.user_id', '=', $this->id)
                    ->distinct();
            });

        return $builder;
    }

    /**
     * @return SupportCollection
     */
    public function getPrivilegesAttribute(): SupportCollection
    {
        return $this->privileges()->get();
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
     * @return BelongsToMany
     */
    public function userAngelTypes(): BelongsToMany
    {
        return $this
            ->belongsToMany(AngelType::class, 'user_angel_type')
            ->using(UserAngelType::class)
            ->withPivot(UserAngelType::getPivotAttributes());
    }

    /**
     * @param AngelType $angelType
     * @return bool
     */
    public function isAngelTypeSupporter(AngelType $angelType): bool
    {
        return $this->userAngelTypes()
            ->wherePivot('angel_type_id', $angelType->id)
            ->wherePivot('supporter', true)
            ->exists();
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

    /**
     * @return HasMany
     */
    public function oauth(): HasMany
    {
        return $this->hasMany(OAuth::class);
    }

    /**
     * @return HasMany
     */
    public function worklogs(): HasMany
    {
        return $this->hasMany(Worklog::class);
    }

    /**
     * @return HasMany
     */
    public function worklogsCreated(): HasMany
    {
        return $this->hasMany(Worklog::class, 'creator_id');
    }

    /**
     * @return HasMany
     */
    public function questionsAsked(): HasMany
    {
        return $this->hasMany(Question::class, 'user_id')
            ->where('user_id', $this->id);
    }

    /**
     * @return HasMany
     */
    public function questionsAnswered(): HasMany
    {
        return $this->hasMany(Question::class, 'answerer_id')
            ->where('answerer_id', $this->id);
    }

    /**
     * @return HasMany
     */
    public function messagesSent(): HasMany
    {
        return $this->hasMany(Message::class, 'user_id')
            ->orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC');
    }

    /**
     * @return HasMany|QueryBuilder
     */
    public function messagesReceived(): HasMany
    {
        return $this->hasMany(Message::class, 'receiver_id')
            ->orderBy('read')
            ->orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC');
    }

    /**
     * Returns a HasMany relation for all messages sent or received by the user.
     *
     * @return HasMany
     */
    public function messages(): HasMany
    {
        return $this->messagesSent()
            ->union($this->messagesReceived())
            ->orderBy('read')
            ->orderBy('id', 'DESC');
    }
}
