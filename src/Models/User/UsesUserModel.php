<?php

namespace Engelsystem\Models\User;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @property int                    $user_id
 *
 * @property-read QueryBuilder|User $user
 *
 * @method static QueryBuilder|static[] whereUserId($value)
 */
trait UsesUserModel
{
    // protected $fillable = ['user_id'];

    /**
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
