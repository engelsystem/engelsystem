<?php

declare(strict_types=1);

namespace Engelsystem\Models;

use Carbon\Carbon;
use Engelsystem\Models\User\UsesUserModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @property int                           $id
 * @property string                        $title
 * @property string                        $text
 * @property bool                          $is_meeting
 * @property bool                          $is_pinned
 * @property Carbon|null                   $created_at
 * @property Carbon|null                   $updated_at
 *
 * @property-read Collection|NewsComment[] $comments
 * @property-read int|null $comments_count
 *
 * @method static QueryBuilder|LogEntry[] whereId($value)
 * @method static QueryBuilder|LogEntry[] whereTitle($value)
 * @method static QueryBuilder|LogEntry[] whereText($value)
 * @method static QueryBuilder|LogEntry[] whereIsMeeting($value)
 * @method static QueryBuilder|LogEntry[] whereIsPinned($value)
 * @method static QueryBuilder|LogEntry[] whereCreatedAt($value)
 * @method static QueryBuilder|LogEntry[] whereUpdatedAt($value)
 */
class News extends BaseModel
{
    use HasFactory;
    use UsesUserModel;

    /** @var bool Enable timestamps */
    public $timestamps = true;

    /** @var array */
    protected $casts = [
        'is_meeting' => 'boolean',
        'is_pinned' => 'boolean',
    ];

    /** @var array */
    protected $fillable = [
        'title',
        'text',
        'is_meeting',
        'is_pinned',
        'user_id',
    ];

    /**
     * @return HasMany
     */
    public function comments(): HasMany
    {
        return $this->hasMany(NewsComment::class)
            ->orderBy('created_at');
    }
}
