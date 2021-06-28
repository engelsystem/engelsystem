<?php

namespace Engelsystem\Models\User;

use Carbon\Carbon;
use Engelsystem\Models\BaseModel;
use Engelsystem\Models\Message;
use Engelsystem\Models\News;
use Engelsystem\Models\NewsComment;
use Engelsystem\Models\OAuth;
use Engelsystem\Models\Question;
use Engelsystem\Models\Worklog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @property int                            $id
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
 * @property-read Collection|News[]         $news
 * @property-read Collection|NewsComment[]  $newsComments
 * @property-read Collection|OAuth[]        $oauth
 * @property-read Collection|Worklog[]      $worklogs
 * @property-read Collection|Worklog[]      $worklogsCreated
 * @property-read int|null                  $news_count
 * @property-read int|null                  $news_comments_count
 * @property-read int|null                  $oauth_count
 * @property-read int|null                  $worklogs_count
 * @property-read int|null                  $worklogs_created_count
 *
 * @method static QueryBuilder|User[] whereId($value)
 * @method static QueryBuilder|User[] whereName($value)
 * @method static QueryBuilder|User[] whereEmail($value)
 * @method static QueryBuilder|User[] wherePassword($value)
 * @method static QueryBuilder|User[] whereApiKey($value)
 * @method static QueryBuilder|User[] whereLastLoginAt($value)
 * @method static QueryBuilder|User[] whereCreatedAt($value)
 * @method static QueryBuilder|User[] whereUpdatedAt($value)
 *
 * @property-read Collection|Question[] $questionsAsked
 * @property-read Collection|Question[] $questionsAnswered
 * @property-read Collection|Message[]  $messagesReceived
 * @property-read Collection|Message[]  $messagesSent
 * @property-read Collection|Message[]  $messages
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
