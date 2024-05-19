<?php

declare(strict_types=1);

namespace Engelsystem\Models\Shifts;

use Engelsystem\Models\AngelType;
use Engelsystem\Models\BaseModel;
use Engelsystem\Models\User\UsesUserModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @property int            $id
 * @property int            $shift_id
 * @property int            $angel_type_id
 * @property string         $user_comment
 * @property bool           $freeloaded
 * @property string         $freeloaded_comment
 *
 * @property-read Shift     $shift
 * @property-read AngelType $angelType
 *
 * @method static QueryBuilder|ShiftEntry[] whereId($value)
 * @method static QueryBuilder|ShiftEntry[] whereShiftId($value)
 * @method static QueryBuilder|ShiftEntry[] whereAngelTypeId($value)
 * @method static QueryBuilder|ShiftEntry[] whereUserComment($value)
 * @method static QueryBuilder|ShiftEntry[] whereFreeloaded($value)
 * @method static QueryBuilder|ShiftEntry[] whereFreeloadedComment($value)
 */
class ShiftEntry extends BaseModel
{
    use HasFactory;
    use UsesUserModel;

    /** @var array<string, string|bool> default attributes */
    protected $attributes = [ // phpcs:ignore
        'user_comment'       => '',
        'freeloaded'         => false,
        'freeloaded_comment' => '',
    ];

    /** @var array<string> */
    protected $fillable = [ // phpcs:ignore
        'shift_id',
        'angel_type_id',
        'user_id',
        'user_comment',
        'freeloaded',
        'freeloaded_comment',
    ];

    /** @var array<string, string> */
    protected $casts = [ // phpcs:ignore
        'shift_id' => 'integer',
        'angel_type_id' => 'integer',
        'user_id' => 'integer',
        'freeloaded' => 'bool',
    ];

    /** @var array<string> Attributes which should not be serialized */
    protected $hidden = [ // phpcs:ignore
        'freeloaded_comment',
    ];

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function angelType(): BelongsTo
    {
        return $this->belongsTo(AngelType::class);
    }
}
