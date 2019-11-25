<?php
declare(strict_types=1);

namespace Engelsystem\Models;

use Engelsystem\Models\User\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * This class represents a message send trough the system.
 *
 * @property integer                            $id
 * @property integer                            $sender_id
 * @property integer                            $receiver_id
 * @property boolean                            $read
 * @property string                             $text
 * @property \Illuminate\Support\Carbon|null    $created_at
 * @property \Illuminate\Support\Carbon|null    $updated_at
 * @property-read \Engelsystem\Models\User\User $receiver
 * @property-read \Engelsystem\Models\User\User $sender
 * @method static \Illuminate\Database\Eloquent\Builder|\Engelsystem\Models\Message newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Engelsystem\Models\Message newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Engelsystem\Models\Message query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Engelsystem\Models\Message whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Engelsystem\Models\Message whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Engelsystem\Models\Message whereRead($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Engelsystem\Models\Message whereReceiverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Engelsystem\Models\Message whereSenderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Engelsystem\Models\Message whereText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Engelsystem\Models\Message whereUpdatedAt($value)
 */
class Message extends BaseModel
{
    /** @var bool enable timestamps */
    public $timestamps = true;

    /** @var string[] */
    protected $casts = [
        'sender_id'   => 'integer',
        'receiver_id' => 'integer',
        'read'        => 'boolean',
    ];

    /** @var string[] */
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'read',
        'text',
    ];

    /** @var array */
    protected $attributes = [
        'read' => false,
    ];

    /**
     * @return BelongsTo
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * @return BelongsTo
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
