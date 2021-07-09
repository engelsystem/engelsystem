<?php

declare(strict_types=1);

namespace Engelsystem\Models;

use Carbon\Carbon;
use Engelsystem\Models\User\UsesUserModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * This class represents a news comment.
 *
 * @property int         $id
 * @property int         $news_id
 * @property string      $text
 * @property News        $news
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static QueryBuilder|NewsComment[] whereId($value)
 * @method static QueryBuilder|NewsComment[] whereNewsId($value)
 * @method static QueryBuilder|NewsComment[] whereText($value)
 * @method static QueryBuilder|NewsComment[] whereCreatedAt($value)
 * @method static QueryBuilder|NewsComment[] whereUpdatedAt($value)
 */
class NewsComment extends BaseModel
{
    use HasFactory;
    use UsesUserModel;

    /** @var bool Enable timestamps */
    public $timestamps = true;

    /** @var string[] */
    protected $fillable = [
        'news_id',
        'text',
        'user_id',
    ];

    /**
     * @return BelongsTo
     */
    public function news(): BelongsTo
    {
        return $this->belongsTo(News::class);
    }
}
