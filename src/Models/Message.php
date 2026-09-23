<?php

declare(strict_types=1);

namespace Engelsystem\Models;

use Engelsystem\Http\Exceptions\HttpForbidden;
use Engelsystem\Models\User\User;
use Engelsystem\Models\User\UsesUserModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

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

    /**
     * The latest message per conversation of the given user, most recent conversation first.
     * Shared between the web and API MessagesControllers - see Message::conversationBetween(),
     * Message::sendMessage() and Message::deleteOwn() below for the other shared operations.
     */
    public static function latestPerConversationFor(User $currentUser): Collection
    {
        $latestMessageIds = static::query()
            ->select(static::query()->getConnection()->raw('max(id) as last_id'))
            ->where('user_id', '=', $currentUser->id)
            ->orWhere('receiver_id', '=', $currentUser->id)
            ->groupBy(static::query()->getConnection()->raw(
                '(CASE WHEN user_id = ' . (int) $currentUser->id . ' THEN receiver_id ELSE user_id END)'
            ));

        return static::query()
            ->joinSub($latestMessageIds, 'conversations', function ($join): void {
                $join->on('messages.id', '=', 'conversations.last_id');
            })
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    /**
     * The number of unread messages per conversation of the given user.
     * @return Collection of unread message amounts, keyed by the other user's id
     */
    public static function unreadCountsPerConversationFor(User $currentUser): Collection
    {
        return $currentUser->messagesReceived()
            ->select('user_id', static::query()->getConnection()->raw('count(*) as amount'))
            ->where('read', false)
            ->groupBy('user_id')
            ->get(['user_id', 'amount'])
            ->mapWithKeys(fn ($unread) => [$unread->user_id => $unread->amount]);
    }

    /**
     * All messages exchanged between the two given users, oldest first. Pure read, no side
     * effects - callers decide themselves whether/how to mark messages read.
     */
    public static function conversationBetween(User $currentUser, User $otherUser): Collection
    {
        return static::query()
            ->where(function ($query) use ($currentUser, $otherUser): void {
                $query->whereUserId($currentUser->id)->whereReceiverId($otherUser->id);
            })
            ->orWhere(function ($query) use ($currentUser, $otherUser): void {
                $query->whereUserId($otherUser->id)->whereReceiverId($currentUser->id);
            })
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Sets or clears the read status of a message. Only the receiver's own "have I read this"
     * state is meaningful to control - the sender may not change it.
     */
    public static function setRead(int $messageId, User $currentUser, bool $read): self
    {
        /** @var self $message */
        $message = static::findOrFail($messageId);

        if ($message->receiver_id != $currentUser->id) {
            throw new HttpForbidden();
        }

        $message->read = $read;
        $message->save();

        return $message;
    }

    /**
     * Sends a message from the sender to the receiver, firing the message.created event.
     */
    public static function sendMessage(User $sender, User $receiver, string $text): self
    {
        $message = new self();
        $message->sender()->associate($sender);
        $message->receiver()->associate($receiver);
        $message->text = $text;
        $message->read = $receiver->id == $sender->id; // if it's to myself, I obviously read it.
        $message->save();

        event('message.created', ['message' => $message]);

        return $message;
    }

    /**
     * Deletes a message, as long as it was sent by the given user.
     */
    public static function deleteOwn(int $messageId, User $currentUser): void
    {
        /** @var self $message */
        $message = static::findOrFail($messageId);

        if ($message->user_id != $currentUser->id) {
            throw new HttpForbidden();
        }

        $message->delete();
    }
}
