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
 * @property int            $confirm_user_id
 * @property bool           $supporter
 *
 * @property-read AngelType $angelType
 * @property-read User      $confirmUser
 *
 * @method static QueryBuilder|AngelType[] whereId($value)
 * @method static QueryBuilder|AngelType[] whereAngelTypeId($value)
 * @method static QueryBuilder|AngelType[] whereConfirmUserId($value)
 * @method static QueryBuilder|AngelType[] whereSupporter($value)
 */
class UserAngelType extends Pivot
{
    use HasFactory;
    use UsesUserModel;

    /** @var bool Increment the IDs */
    public $incrementing = true;

    /** @var bool Disable timestamps */
    public $timestamps = false;

    /** @var array */
    protected $fillable = [
        'user_id',
        'angel_type_id',
        'confirm_user_id',
        'supporter',
    ];

    /** @var string[] */
    protected $casts = [
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
}
