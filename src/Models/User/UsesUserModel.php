<?php

declare(strict_types=1);

namespace Engelsystem\Models\User;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int       $user_id
 *
 * @property-read User $user
 *
 * @method static Builder|static[] whereUserId($value)
 */
trait UsesUserModel
{
    // protected $fillable = ['user_id'];
    // protected $casts = ['user_id' => 'integer];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
