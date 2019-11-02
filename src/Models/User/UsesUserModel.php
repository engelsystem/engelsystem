<?php

namespace Engelsystem\Models\User;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property integer                                                               $user_id
 *
 * @property-read \Illuminate\Database\Query\Builder|\Engelsystem\Models\User\User $user
 *
 * @method static \Illuminate\Database\Query\Builder|static whereUserId($value)
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
