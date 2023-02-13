<?php

declare(strict_types=1);

namespace Engelsystem\Models;

use Carbon\Carbon;
use Engelsystem\Models\User\UsesUserModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Str;

/**
 * @property int                           $id
 * @property string                        $title
 * @property string                        $text
 * @property bool                          $is_meeting
 * @property bool                          $is_pinned
 * @property bool                          $is_important
 * @property Carbon|null                   $created_at
 * @property Carbon|null                   $updated_at
 *
 * @property-read Collection|NewsComment[] $comments
 * @property-read int|null                 $comments_count
 *
 * @method static QueryBuilder|News[] whereId($value)
 * @method static QueryBuilder|News[] whereTitle($value)
 * @method static QueryBuilder|News[] whereText($value)
 * @method static QueryBuilder|News[] whereIsMeeting($value)
 * @method static QueryBuilder|News[] whereIsPinned($value)
 * @method static QueryBuilder|News[] whereIsImportant($value)
 * @method static QueryBuilder|News[] whereCreatedAt($value)
 * @method static QueryBuilder|News[] whereUpdatedAt($value)
 */
class News extends BaseModel
{
    use HasFactory;
    use UsesUserModel;

    /** @var bool Enable timestamps */
    public $timestamps = true; // phpcs:ignore

    /** @var array<string, string> */
    protected $casts = [ // phpcs:ignore
        'user_id'      => 'integer',
        'is_meeting'   => 'boolean',
        'is_pinned'    => 'boolean',
        'is_important' => 'boolean',
    ];

    /** @var array<string, bool> Default attributes */
    protected $attributes = [ // phpcs:ignore
        'is_meeting'   => false,
        'is_pinned'    => false,
        'is_important' => false,
    ];

    /** @var array<string> */
    protected $fillable = [ // phpcs:ignore
        'title',
        'text',
        'is_meeting',
        'is_pinned',
        'is_important',
        'user_id',
    ];

    public function comments(): HasMany
    {
        return $this->hasMany(NewsComment::class)
            ->orderBy('created_at');
    }

    public function text(bool $showMore = true): string
    {
        if ($showMore || !Str::contains($this->text, 'more')) {
            // Remove more tag
            return preg_replace('/(.*)\[\s*more\s*\](.*)/is', '$1$2', $this->text);
        }

        // Only show text before more tag
        $text = preg_replace('/(.*)(\s*\[\s*more\s*\].*)/is', '$1', $this->text);
        return rtrim($text);
    }
}
