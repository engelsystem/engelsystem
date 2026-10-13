<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Models;

use Engelsystem\Events\EventDispatcher;
use Engelsystem\Http\Exceptions\HttpForbidden;
use Engelsystem\Models\Message;
use Engelsystem\Models\User\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use PHPUnit\Framework\Attributes\CoversMethod;

/**
 * This class provides tests covering the Message model and its relations.
 */
#[CoversMethod(Message::class, 'sender')]
#[CoversMethod(Message::class, 'receiver')]
#[CoversMethod(Message::class, 'latestPerConversationFor')]
#[CoversMethod(Message::class, 'unreadCountsPerConversationFor')]
#[CoversMethod(Message::class, 'conversationBetween')]
#[CoversMethod(Message::class, 'setRead')]
#[CoversMethod(Message::class, 'sendMessage')]
#[CoversMethod(Message::class, 'deleteOwn')]
#[CoversMethod(User::class, 'messagesSent')]
#[CoversMethod(User::class, 'messagesReceived')]
#[CoversMethod(User::class, 'messages')]
class MessageTest extends ModelTestCase
{
    private User $user1;

    private User $user2;

    private Message $message1;

    private Message $message2;

    private Message $message3;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user1 = User::factory()->create();
        $this->user2 = User::factory()->create();

        $this->message1 = Message::create([
            'user_id'     => $this->user1->id,
            'receiver_id' => $this->user2->id,
            'text'        => 'message1',
        ]);

        $this->message2 = Message::create([
            'user_id'     => $this->user1->id,
            'receiver_id' => $this->user2->id,
            'read'        => true,
            'text'        => 'message2',
        ]);

        $this->message3 = Message::create([
            'user_id'     => $this->user2->id,
            'receiver_id' => $this->user1->id,
            'text'        => 'message3',
        ]);
    }

    /**
     * Tests that loading Messages works.
     */
    public function testLoad(): void
    {
        $message1 = Message::find($this->message1->id);
        $this->assertSame($this->message1->user_id, $message1->user_id);
        $this->assertSame($this->message1->receiver_id, $message1->receiver_id);
        $this->assertSame($this->message1->read, $message1->read);
        $this->assertSame($this->message1->text, $message1->text);

        $message2 = Message::find($this->message2->id);
        $this->assertSame($this->message2->user_id, $message2->user_id);
        $this->assertSame($this->message2->receiver_id, $message2->receiver_id);
        $this->assertSame($this->message2->read, $message2->read);
        $this->assertSame($this->message2->text, $message2->text);
    }

    /**
     * Tests that the Messages have the correct senders.
     */
    public function testSenders(): void
    {
        $this->assertSame($this->user1->id, $this->message1->user->id);
        $this->assertSame($this->user1->id, $this->message2->user->id);
        $this->assertSame($this->user2->id, $this->message3->user->id);

        $this->assertSame($this->user1->id, $this->message1->sender->id);
        $this->assertSame($this->user1->id, $this->message2->sender->id);
        $this->assertSame($this->user2->id, $this->message3->sender->id);
    }

    /**
     * Tests that the Messages have the correct receivers.
     */
    public function testReceivers(): void
    {
        $this->assertSame($this->user2->id, $this->message1->receiver->id);
        $this->assertSame($this->user2->id, $this->message2->receiver->id);
        $this->assertSame($this->user1->id, $this->message3->receiver->id);
    }

    /**
     * Tests that the Users have the correct sent Messages.
     */
    public function testUserSentMessages(): void
    {
        $sentByUser1 = $this->user1->messagesSent->all();
        $this->assertCount(2, $sentByUser1);
        $this->assertSame($this->message2->id, $sentByUser1[0]->id);
        $this->assertSame($this->message1->id, $sentByUser1[1]->id);

        $sentByUser2 = $this->user2->messagesSent->all();
        $this->assertCount(1, $sentByUser2);
        $this->assertSame($this->message3->id, $sentByUser2[0]->id);
    }

    /**
     * Tests that the Users have the correct received Messages.
     */
    public function testUserReceivedMessages(): void
    {
        $receivedByUser1 = $this->user1->messagesReceived->all();
        $this->assertCount(1, $receivedByUser1);
        $this->assertSame($this->message3->id, $receivedByUser1[0]->id);

        $receivedByUser2 = $this->user2->messagesReceived->all();
        $this->assertCount(2, $receivedByUser2);
        $this->assertSame($this->message1->id, $receivedByUser2[0]->id);
        $this->assertSame($this->message2->id, $receivedByUser2[1]->id);
    }

    /**
     * Tests that the user have the correct Messages.
     */
    public function testUserMessages(): void
    {
        $user1Messages = $this->user1->messages->all();
        $this->assertCount(3, $user1Messages);
        $this->assertSame($this->message3->id, $user1Messages[0]->id);
        $this->assertSame($this->message1->id, $user1Messages[1]->id);
        $this->assertSame($this->message2->id, $user1Messages[2]->id);

        $user2Messages = $this->user2->messages->all();
        $this->assertCount(3, $user2Messages);
        $this->assertSame($this->message3->id, $user2Messages[0]->id);
        $this->assertSame($this->message1->id, $user2Messages[1]->id);
        $this->assertSame($this->message2->id, $user2Messages[2]->id);
    }

    public function testLatestPerConversationFor(): void
    {
        // user1 has a single conversation partner (user2) across all three fixture messages,
        // regardless of who sent which - only the most recent of the three should appear.
        $latest = Message::latestPerConversationFor($this->user1);

        $this->assertCount(1, $latest);
        $this->assertSame($this->message3->id, $latest->first()->id);
    }

    public function testUnreadCountsPerConversationFor(): void
    {
        // user2 received message1 (unread) and message2 (read) from user1.
        $unread = Message::unreadCountsPerConversationFor($this->user2);

        $this->assertSame(1, $unread[$this->user1->id]);
    }

    public function testConversationBetweenIsOrderedAndHasNoSideEffects(): void
    {
        // All three fixture messages are between user1 and user2, in either direction.
        $messages = Message::conversationBetween($this->user1, $this->user2);

        $this->assertCount(3, $messages);
        $this->assertSame($this->message1->id, $messages[0]->id);
        $this->assertSame($this->message2->id, $messages[1]->id);
        $this->assertSame($this->message3->id, $messages[2]->id);

        // Pure read - no side effects on the read status.
        $this->assertFalse(Message::find($this->message1->id)->read);
        $this->assertFalse(Message::find($this->message3->id)->read);
    }

    public function testSetReadBySetsAndClears(): void
    {
        $updated = Message::setRead($this->message1->id, $this->user2, true);
        $this->assertTrue($updated->read);
        $this->assertTrue(Message::find($this->message1->id)->read);

        $updated = Message::setRead($this->message1->id, $this->user2, false);
        $this->assertFalse($updated->read);
        $this->assertFalse(Message::find($this->message1->id)->read);
    }

    public function testSetReadForbiddenForSender(): void
    {
        $this->expectException(HttpForbidden::class);
        Message::setRead($this->message1->id, $this->user1, true);
    }

    public function testSetReadNotFound(): void
    {
        $this->expectException(ModelNotFoundException::class);
        Message::setRead(999999, $this->user1, true);
    }

    public function testSendMessage(): void
    {
        $events = $this->createMock(EventDispatcher::class);
        $this->app->instance('events.dispatcher', $events);
        $this->setExpects($events, 'dispatch', ['message.created'], []);

        $message = Message::sendMessage($this->user1, $this->user2, 'hello');

        $this->assertSame($this->user1->id, $message->user_id);
        $this->assertSame($this->user2->id, $message->receiver_id);
        $this->assertSame('hello', $message->text);
        $this->assertFalse($message->read);
    }

    public function testSendMessageToSelfIsAlreadyRead(): void
    {
        $events = $this->createMock(EventDispatcher::class);
        $this->app->instance('events.dispatcher', $events);
        $this->setExpects($events, 'dispatch', ['message.created'], []);

        $message = Message::sendMessage($this->user1, $this->user1, 'note to self');

        $this->assertTrue($message->read);
    }

    public function testDeleteOwnDeletesMessage(): void
    {
        $id = $this->message1->id;
        Message::deleteOwn($id, $this->user1);

        $this->assertNull(Message::find($id));
    }

    public function testDeleteOwnForbiddenForNonSender(): void
    {
        $this->expectException(HttpForbidden::class);
        Message::deleteOwn($this->message1->id, $this->user2);
    }

    public function testDeleteOwnNotFound(): void
    {
        $this->expectException(ModelNotFoundException::class);
        Message::deleteOwn(999999, $this->user1);
    }
}
