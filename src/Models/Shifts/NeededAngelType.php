<?php

declare(strict_types=1);

namespace Engelsystem\Models\Shifts;

use Engelsystem\Models\AngelType;
use Engelsystem\Models\BaseModel;
use Engelsystem\Models\Room;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @property int             $id
 * @property int|null        $room_id
 * @property int|null        $shift_id
 * @property int             $angel_type_id
 * @property int             $count
 *
 * @property-read Room|null  $room
 * @property-read Shift|null $shift
 * @property-read AngelType  $angelType
 *
 * @method static QueryBuilder|NeededAngelType[] whereId($value)
 * @method static QueryBuilder|NeededAngelType[] whereRoomId($value)
 * @method static QueryBuilder|NeededAngelType[] whereShiftId($value)
 * @method static QueryBuilder|NeededAngelType[] whereAngelTypeId($value)
 * @method static QueryBuilder|NeededAngelType[] whereCount($value)
 */
class NeededAngelType extends BaseModel
{
    use HasFactory;

    /** @var array<string> */
    protected $fillable = [ // phpcs:ignore
        'room_id',
        'shift_id',
        'angel_type_id',
        'count',
    ];

    /** @var array<string, null> default attributes */
    protected $attributes = [ // phpcs:ignore
        'room_id'  => null,
        'shift_id' => null,
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function angelType(): BelongsTo
    {
        return $this->belongsTo(AngelType::class);
    }
}
