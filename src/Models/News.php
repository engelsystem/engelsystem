<?php

declare(strict_types=1);

namespace Engelsystem\Models;

use Carbon\Carbon;
use Engelsystem\Models\User\UsesUserModel;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @property int         $id
 * @property string      $title
 * @property string      $text
 * @property bool        $is_meeting
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static QueryBuilder|LogEntry[] whereId($value)
 * @method static QueryBuilder|LogEntry[] whereTitle($value)
 * @method static QueryBuilder|LogEntry[] whereText($value)
 * @method static QueryBuilder|LogEntry[] whereIsMeeting($value)
 * @method static QueryBuilder|LogEntry[] whereCreatedAt($value)
 * @method static QueryBuilder|LogEntry[] whereUpdatedAt($value)
 */
class News extends BaseModel
{
    use UsesUserModel;

    /** @var bool Enable timestamps */
    public $timestamps = true;

    /** @var array */
    protected $casts = [
        'is_meeting' => 'boolean',
    ];

    /** @var array */
    protected $fillable = [
        'title',
        'text',
        'is_meeting',
        'user_id',
    ];
}
