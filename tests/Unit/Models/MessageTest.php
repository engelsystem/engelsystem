<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Models;

use Engelsystem\Models\Message;
use Engelsystem\Models\User\User;

/**
 * This class provides tests covering the Message model and its relations.
 */
class MessageTest extends ModelTest
{
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
     *
     * @covers \Engelsystem\Models\Message::__construct
     *
     * @return void
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
     *
     * @covers \Engelsystem\Models\Message::user
     * @covers \Engelsystem\Models\Message::sender
     *
     * @return void
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
     *
     * @covers \Engelsystem\Models\Message::receiver
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
     * @covers \Engelsystem\Models\User\User::messagesSent
     *
     * @return void
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
     *
     * @covers \Engelsystem\Models\User\User::messagesReceived
     *
     * @return void
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
     *
     * @covers \Engelsystem\Models\User\User::messages
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
