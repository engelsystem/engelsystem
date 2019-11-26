<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Models;

use Engelsystem\Models\Message;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\TestCase;

/**
 * This class provides tests covering the Message model and its relations.
 */
class MessageTest extends TestCase
{
    use HasDatabase;

    /** @var User */
    private $user1;

    /** @var User */
    private $user2;

    /** @var Message */
    private $message1;

    /** @var Message */
    private $message2;

    /** @var Message */
    private $message3;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->initDatabase();

        $this->user1 = User::create([
            'name'     => 'user1',
            'password' => '',
            'email'    => 'user1@example.com',
            'api_key'  => '',
        ]);

        $this->user2 = User::create([
            'name'     => 'user2',
            'password' => '',
            'email'    => 'user2@example.com',
            'api_key'  => '',
        ]);

        $this->message1 = Message::create([
            'sender_id'   => $this->user1->id,
            'receiver_id' => $this->user2->id,
            'text'        => 'message1',
        ]);

        $this->message2 = Message::create([
            'sender_id'   => $this->user1->id,
            'receiver_id' => $this->user2->id,
            'read'        => true,
            'text'        => 'message2',
        ]);

        $this->message3 = Message::create([
            'sender_id'   => $this->user2->id,
            'receiver_id' => $this->user1->id,
            'text'        => 'message3',
        ]);
    }

    /**
     * Tests that loading Messages works.
     *
     * @return void
     */
    public function testLoad(): void
    {
        $message1 = Message::find($this->message1->id);
        $this->assertSame($this->message1->sender_id, $message1->sender_id);
        $this->assertSame($this->message1->receiver_id, $message1->receiver_id);
        $this->assertSame($this->message1->read, $message1->read);
        $this->assertSame($this->message1->text, $message1->text);

        $message2 = Message::find($this->message2->id);
        $this->assertSame($this->message2->sender_id, $message2->sender_id);
        $this->assertSame($this->message2->receiver_id, $message2->receiver_id);
        $this->assertSame($this->message2->read, $message2->read);
        $this->assertSame($this->message2->text, $message2->text);
    }

    /**
     * Tests that the Messages have the correct senders.
     *
     * @return void
     */
    public function testSenders(): void
    {
        $this->assertSame($this->user1->id, $this->message1->sender->id);
        $this->assertSame($this->user1->id, $this->message2->sender->id);
        $this->assertSame($this->user2->id, $this->message3->sender->id);
    }

    /**
     * Tests that the Messages have the correct receivers.
     *
     * @return void
     */
    public function testReceivers(): void
    {
        $this->assertSame($this->user2->id, $this->message1->receiver->id);
        $this->assertSame($this->user2->id, $this->message2->receiver->id);
        $this->assertSame($this->user1->id, $this->message3->receiver->id);
    }

    /**
     * Tests that the Users have the correct sent Messages.
     *
     * @return void
     */
    public function testUserSentMessages(): void
    {
        $sentByUser1 = $this->user1->sentMessages->all();
        $this->assertCount(2, $sentByUser1);
        $this->assertSame($this->message2->id, $sentByUser1[0]->id);
        $this->assertSame($this->message1->id, $sentByUser1[1]->id);

        $sentByUser2 = $this->user2->sentMessages->all();
        $this->assertCount(1, $sentByUser2);
        $this->assertSame($this->message3->id, $sentByUser2[0]->id);
    }

    /**
     * Tests that the Users have the correct received Messages.
     *
     * @return void
     */
    public function testUserReceivedMessages(): void
    {
        $receivedByUser1 = $this->user1->receivedMessages->all();
        $this->assertCount(1, $receivedByUser1);
        $this->assertSame($this->message3->id, $receivedByUser1[0]->id);

        $receivedByUser2 = $this->user2->receivedMessages->all();
        $this->assertCount(2, $receivedByUser2);
        $this->assertSame($this->message1->id, $receivedByUser2[0]->id);
        $this->assertSame($this->message2->id, $receivedByUser2[1]->id);
    }

    /**
     * Tests that the user have the correct received and unread Messages.
     *
     * @return void
     */
    public function testUserReceivedUnreadMessages(): void
    {
        $receivedUnreadByUser1 = $this->user1->receivedUnreadMessages->all();
        $this->assertCount(1, $receivedUnreadByUser1);
        $this->assertSame($this->message3->id, $receivedUnreadByUser1[0]->id);

        $receivedUnreadByUser2 = $this->user2->receivedUnreadMessages->all();
        $this->assertCount(1, $receivedUnreadByUser2);
        $this->assertSame($this->message1->id, $receivedUnreadByUser2[0]->id);
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
}
