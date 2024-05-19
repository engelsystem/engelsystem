<?php

declare(strict_types=1);

namespace Engelsystem\Models;

use Engelsystem\Models\User\User;
use Engelsystem\Models\User\UsesUserModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * This class represents a message send trough the system.
 *
 * @property int         $id
 * @property int         $receiver_id
 * @property bool        $read
 * @property string      $text
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read User   $sender
 * @property-read User   $receiver
 *
 * @method static Builder|Message whereId($value)
 * @method static Builder|Message whereUserId($value)
 * @method static Builder|Message whereReceiverId($value)
 * @method static Builder|Message whereRead($value)
 * @method static Builder|Message whereText($value)
 * @method static Builder|Message whereCreatedAt($value)
 * @method static Builder|Message whereUpdatedAt($value)
 */
class Message extends BaseModel
{
    use HasFactory;
    use UsesUserModel;

    /** @var bool enable timestamps */
    public $timestamps = true; // phpcs:ignore

    /** @var array<string, string> */
    protected $casts = [ // phpcs:ignore
        'user_id'     => 'integer',
        'receiver_id' => 'integer',
        'read'        => 'boolean',
    ];

    /** @var array<string> */
    protected $fillable = [ // phpcs:ignore
        'user_id',
        'receiver_id',
        'read',
        'text',
    ];

    /** @var array<string, bool> */
    protected $attributes = [ // phpcs:ignore
        'read' => false,
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
