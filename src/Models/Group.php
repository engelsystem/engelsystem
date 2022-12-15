<?php

declare(strict_types=1);

namespace Engelsystem\Models;

use Engelsystem\Models\User\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int                         $id
 * @property string                      $name
 *
 * @property-read Collection|Privilege[] $privileges
 * @property-read Collection|User[]      $users
 *
 * @method static Builder|Group whereId($value)
 * @method static Builder|Group whereName($value)
 */
class Group extends BaseModel
{
    use HasFactory;

    /** @var string[] */
    protected $fillable = [ // phpcs:ignore
        'name',
    ];

    public function privileges(): BelongsToMany
    {
        return $this->belongsToMany(Privilege::class, 'group_privileges');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'users_groups');
    }
}
