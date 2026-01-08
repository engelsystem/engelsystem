<?php

declare(strict_types=1);

namespace Engelsystem\Models\Shifts;

use Engelsystem\Models\AngelType;
use Engelsystem\Models\BaseModel;
use Engelsystem\Models\User\User;
use Engelsystem\Models\User\UsesUserModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @property int            $id
 * @property int            $shift_id
 * @property int            $angel_type_id
 * @property string         $user_comment
 * @property int|null       $freeloaded_by
 * @property string         $freeloaded_comment
 * @property bool           $counts_toward_quota
 * @property int|null       $supervised_by_user_id
 *
 * @property-read Shift     $shift
 * @property-read AngelType $angelType
 * @property-read User|null $freeloadedBy
 * @property-read User|null $supervisedBy
 *
 * @method static QueryBuilder|ShiftEntry[] whereId($value)
 * @method static QueryBuilder|ShiftEntry[] whereShiftId($value)
 * @method static QueryBuilder|ShiftEntry[] whereAngelTypeId($value)
 * @method static QueryBuilder|ShiftEntry[] whereUserComment($value)
 * @method static QueryBuilder|ShiftEntry[] whereFreeloadedBy($value)
 * @method static QueryBuilder|ShiftEntry[] whereFreeloadedComment($value)
 * @method static QueryBuilder|ShiftEntry[] whereCountsTowardQuota($value)
 * @method static QueryBuilder|ShiftEntry[] whereSupervisedByUserId($value)
 */
class ShiftEntry extends BaseModel
{
    use HasFactory;
    use UsesUserModel;

    /** @var array<string, string|null|bool> default attributes */
    protected $attributes = [ // phpcs:ignore
        'user_comment'           => '',
        'freeloaded_by'          => null,
        'freeloaded_comment'     => '',
        'counts_toward_quota'    => true,
        'supervised_by_user_id'  => null,
    ];

    /** @var array<string> */
    protected $fillable = [ // phpcs:ignore
        'shift_id',
        'angel_type_id',
        'user_id',
        'user_comment',
        'freeloaded_by',
        'freeloaded_comment',
        'counts_toward_quota',
        'supervised_by_user_id',
    ];

    /** @var array<string, string> */
    protected $casts = [ // phpcs:ignore
        'shift_id' => 'integer',
        'angel_type_id' => 'integer',
        'user_id' => 'integer',
        'freeloaded_by'          => 'integer',
        'counts_toward_quota'    => 'boolean',
        'supervised_by_user_id'  => 'integer',
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

    public function freeloadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'freeloaded_by');
    }

    public function supervisedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervised_by_user_id');
    }
}
