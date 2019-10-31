<?php
declare(strict_types=1);

namespace Engelsystem\Models\News;

use Engelsystem\Models\User\UsesUserModel;
use Illuminate\Database\Eloquent\Model;

/**
 * This class represents a news item.
 */
class News extends Model
{
    use UsesUserModel;

    protected $casts = [
        'is_meeting' => 'boolean',
    ];

    protected $attributes = [
        'is_meeting' => false,
    ];

    protected $fillable = [
        'title',
        'text',
        'is_meeting',
        'user_id',
    ];
}
