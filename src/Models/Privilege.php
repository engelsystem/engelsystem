<?php

declare(strict_types=1);

namespace Engelsystem\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int                     $id
 * @property string                  $name
 * @property string                  $description
 *
 * @property-read Collection|Group[] $groups
 *
 * @method static Builder|Privilege whereId($value)
 * @method static Builder|Privilege whereName($value)
 * @method static Builder|Privilege whereDescription($value)
 */
class Privilege extends BaseModel
{
    use HasFactory;

    /** @var array<string> */
    protected $fillable = [ // phpcs:ignore
        'name',
        'description',
    ];

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_privileges');
    }
}
