<?php

namespace Engelsystem\Models\User;

use Engelsystem\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property integer                                                               $user_id
 *
 * @property-read \Illuminate\Database\Query\Builder|\Engelsystem\Models\User\User $user
 *
 * @method static \Illuminate\Database\Query\Builder|static whereUserId($value)
 */
abstract class HasUserModel extends BaseModel
{
    /** @var string The primary key for the model */
    protected $primaryKey = 'user_id';

    /** The attributes that are mass assignable */
    protected $fillable = [
        'user_id',
    ];

    /** The relationships that should be touched on save */
    protected $touches = ['user'];

    /**
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
