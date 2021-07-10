<?php

namespace Engelsystem\Models;

use Carbon\Carbon;
use Engelsystem\Models\User\User;
use Engelsystem\Models\User\UsesUserModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @property int         $id
 * @property int         $creator_id
 * @property float       $hours
 * @property string      $comment
 * @property Carbon      $worked_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read User $creator
 *
 * @method static QueryBuilder|Worklog[] whereId($value)
 * @method static QueryBuilder|Worklog[] whereCreatorId($value)
 * @method static QueryBuilder|Worklog[] whereWorkedAt($value)
 * @method static QueryBuilder|Worklog[] whereHours($value)
 * @method static QueryBuilder|Worklog[] whereComment($value)
 */
class Worklog extends BaseModel
{
    use HasFactory;
    use UsesUserModel;

    /** @var bool Enable timestamps */
    public $timestamps = true;

    /** @var array The attributes that should be mutated to dates */
    protected $dates = [
        'worked_at',
    ];

    /** @var string[] */
    protected $casts = [
        'user_id'    => 'integer',
        'creator_id' => 'integer',
    ];

    /** The attributes that are mass assignable. */
    protected $fillable = [
        'user_id',
        'creator_id',
        'hours',
        'comment',
        'worked_at',
    ];

    /**
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
}
