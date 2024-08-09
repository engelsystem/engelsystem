<?php

declare(strict_types=1);

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
use Engelsystem\Models\Session;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftEntry;
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
 * @property Carbon|null                        $created_at
 * @property Carbon|null                        $updated_at
 *
 * @property-read QueryBuilder|Contact          $contact
 * @property-read QueryBuilder|License          $license
 * @property-read QueryBuilder|PersonalData     $personalData
 * @property-read QueryBuilder|Settings         $settings
 * @property-read QueryBuilder|State            $state
 * @property-read string                        $displayName
 *
 * @property-read Collection|Group[]            $groups
 * @property-read Collection|News[]             $news
 * @property-read Collection|NewsComment[]      $newsComments
 * @property-read Collection|OAuth[]            $oauth
 * @property-read SupportCollection|Privilege[] $privileges
 * @property-read Collection|AngelType[]        $userAngelTypes
 * @property-read UserAngelType                 $pivot
 * @property-read Collection|ShiftEntry[]       $shiftEntries
 * @property-read Collection|Session[]          $sessions
 * @property-read Collection|Worklog[]          $worklogs
 * @property-read Collection|Worklog[]          $worklogsCreated
 * @property-read Collection|Question[]         $questionsAsked
 * @property-read Collection|Question[]         $questionsAnswered
 * @property-read Collection|Message[]          $messagesReceived
 * @property-read Collection|Message[]          $messagesSent
 * @property-read Collection|Message[]          $messages
 * @property-read Collection|Shift[]            $shiftsCreated
 * @property-read Collection|Shift[]            $shiftsUpdated
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
    public $timestamps = true; // phpcs:ignore

    /** @var array<string, null> default attributes */
    protected $attributes = [ // phpcs:ignore
        'last_login_at' => null,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [ // phpcs:ignore
        'name',
        'password',
        'email',
        'api_key',
        'last_login_at',
    ];

    /** @var array<string> The attributes that should be hidden for serialization */
    protected $hidden = [ // phpcs:ignore
        'api_key',
        'password',
    ];

    /** @var array<string, string> */
    protected $casts = [ // phpcs:ignore
        'last_login_at' => 'datetime',
    ];

    public function contact(): HasOne
    {
        return $this
            ->hasOne(Contact::class)
            ->withDefault();
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'users_groups');
    }

    public function isFreeloader(): bool
    {
        return $this->shiftEntries()
                ->where('freeloaded', true)
                ->count()
            >= config('max_freeloadable_shifts');
    }

    public function license(): HasOne
    {
        return $this
            ->hasOne(License::class)
            ->withDefault();
    }

    public function privileges(): Builder
    {
        /** @var Builder $builder */
        $builder = Privilege::query()
            ->whereIn('id', function ($query): void {
                /** @var QueryBuilder $query */
                $query->select('privilege_id')
                    ->from('group_privileges')
                    ->join('users_groups', 'users_groups.group_id', '=', 'group_privileges.group_id')
                    ->where('users_groups.user_id', '=', $this->id)
                    ->distinct();
            });

        return $builder;
    }

    public function getPrivilegesAttribute(): SupportCollection
    {
        return $this->privileges()->get();
    }

    public function personalData(): HasOne
    {
        return $this
            ->hasOne(PersonalData::class)
            ->withDefault();
    }

    public function settings(): HasOne
    {
        return $this
            ->hasOne(Settings::class)
            ->withDefault();
    }

    public function state(): HasOne
    {
        return $this
            ->hasOne(State::class)
            ->withDefault();
    }

    public function userAngelTypes(): BelongsToMany
    {
        return $this
            ->belongsToMany(AngelType::class, 'user_angel_type')
            ->using(UserAngelType::class)
            ->withPivot(UserAngelType::getPivotAttributes());
    }

    public function isAngelTypeSupporter(AngelType $angelType): bool
    {
        return $this->userAngelTypes()
            ->wherePivot('angel_type_id', $angelType->id)
            ->wherePivot('supporter', true)
            ->exists();
    }

    public function news(): HasMany
    {
        return $this->hasMany(News::class);
    }

    public function newsComments(): HasMany
    {
        return $this->hasMany(NewsComment::class);
    }

    public function oauth(): HasMany
    {
        return $this->hasMany(OAuth::class);
    }

    public function shiftEntries(): HasMany
    {
        return $this->hasMany(ShiftEntry::class);
    }

    public function worklogs(): HasMany
    {
        return $this->hasMany(Worklog::class);
    }

    public function worklogsCreated(): HasMany
    {
        return $this->hasMany(Worklog::class, 'creator_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }

    public function questionsAsked(): HasMany
    {
        return $this->hasMany(Question::class, 'user_id')
            ->where('user_id', $this->id);
    }

    public function questionsAnswered(): HasMany
    {
        return $this->hasMany(Question::class, 'answerer_id')
            ->where('answerer_id', $this->id);
    }

    public function messagesSent(): HasMany
    {
        return $this->hasMany(Message::class, 'user_id')
            ->orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC');
    }

    public function messagesReceived(): HasMany
    {
        return $this->hasMany(Message::class, 'receiver_id')
            ->orderBy('read')
            ->orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC');
    }

    /**
     * Returns a HasMany relation for all messages sent or received by the user.
     */
    public function messages(): HasMany
    {
        return $this->messagesSent()
            ->union($this->messagesReceived())
            ->orderBy('read')
            ->orderBy('id', 'DESC');
    }

    public function shiftsCreated(): HasMany
    {
        return $this->hasMany(Shift::class, 'created_by');
    }

    public function shiftsUpdated(): HasMany
    {
        return $this->hasMany(Shift::class, 'updated_by');
    }

    public function getDisplayNameAttribute(): string
    {
        if (
            config('display_full_name')
            && !empty(trim($this->personalData->first_name . $this->personalData->last_name))
        ) {
            return trim(
                trim((string) $this->personalData->first_name)
                . ' ' .
                trim((string) $this->personalData->last_name)
            );
        }

        return $this->name;
    }
}
