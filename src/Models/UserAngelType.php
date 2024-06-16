<?php

declare(strict_types=1);

namespace Engelsystem\Models;

use Engelsystem\Models\User\User;
use Engelsystem\Models\User\UsesUserModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @mixin Builder
 *
 * @property int            $id
 * @property int            $angel_type_id
 * @property int|null       $confirm_user_id
 * @property bool           $supporter
 *
 * @property-read AngelType $angelType
 * @property-read User|null $confirmUser
 * @property-read bool      $isConfirmed
 *
 * @method static QueryBuilder|UserAngelType[] whereId($value)
 * @method static QueryBuilder|UserAngelType[] whereAngelTypeId($value)
 * @method static QueryBuilder|UserAngelType[] whereConfirmUserId($value)
 * @method static QueryBuilder|UserAngelType[] whereSupporter($value)
 */
class UserAngelType extends Pivot
{
    use HasFactory;
    use UsesUserModel;

    /** @var bool Increment the IDs */
    public $incrementing = true; // phpcs:ignore

    /** @var bool Disable timestamps */
    public $timestamps = false; // phpcs:ignore

    /** @var array<string, null|bool> default attributes */
    protected $attributes = [ // phpcs:ignore
        'confirm_user_id' => null,
        'supporter'       => false,
    ];

    /** @var array<string> */
    protected $fillable = [ // phpcs:ignore
        'user_id',
        'angel_type_id',
        'confirm_user_id',
        'supporter',
    ];

    /** @var array<string> */
    protected $casts = [ // phpcs:ignore
        'user_id'         => 'integer',
        'angel_type_id'   => 'integer',
        'confirm_user_id' => 'integer',
        'supporter'       => 'boolean',
    ];

    /**
     * Returns a list of attributes that can be requested for this pivot table
     *
     * @return string[]
     */
    public static function getPivotAttributes(): array
    {
        return ['id', 'confirm_user_id', 'supporter'];
    }

    public function angelType(): BelongsTo
    {
        return $this->belongsTo(AngelType::class);
    }

    public function confirmUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirm_user_id');
    }

    public function getIsConfirmedAttribute(): bool
    {
        return !$this->angelType->restricted || $this->confirm_user_id;
    }
}
