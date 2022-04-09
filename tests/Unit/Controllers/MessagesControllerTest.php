<?php

namespace Engelsystem\Test\Unit\Controllers;

use Carbon\Carbon;
use Engelsystem\Controllers\MessagesController;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Exceptions\HttpForbidden;
use Engelsystem\Http\Exceptions\ValidationException;
use Engelsystem\Http\UrlGenerator;
use Engelsystem\Http\UrlGeneratorInterface;
use Engelsystem\Http\Validation\Validator;
use Engelsystem\Models\Message;
use Engelsystem\Models\Question;
use Engelsystem\Models\User\PersonalData;
use Engelsystem\Models\User\Settings;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\HasDatabase;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use PHPUnit\Framework\MockObject\MockObject;

class MessagesControllerTest extends ControllerTest
{
    use HasDatabase;

    /** @var MessagesController */
    protected $controller;

    /** @var Authenticator|MockObject */
    protected $auth;

    /** @var User */
    protected $user_a;
    /** @var User */
    protected $user_b;

    /** @var Carbon */
    protected $now;
    /** @var Carbon */
    protected $one_minute_ago;
    /** @var Carbon */
    protected $two_minutes_ago;

    /**
     * @testdox index: underNormalConditions -> returnsCorrectViewAndData
     */
    public function testIndexUnderNormalConditionsReturnsCorrectViewAndData()
    {
        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $this->assertEquals('pages/messages/overview.twig', $view);
                $this->assertArrayHasKey('conversations', $data);
                $this->assertArrayHasKey('users', $data);
                $this->assertArrayOrCollection($data['conversations']);
                $this->assertArrayOrCollection($data['users']);
                return $this->response;
            });

        $this->controller->index();
    }

    /**
     * @testdox index: otherUsersExist -> returnsUsersWithoutMeOrderedByName
     */
    public function testIndexOtherUsersExistReturnsUsersWithoutMeOrderedByName()
    {
        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $users = $data['users'];

                $this->assertEquals(1, count($users));
                $this->assertEquals('b', $users[$this->user_b->id]);

                return $this->response;
            });

        $this->controller->index();
    }

    /**
     * @testdox index: pronounsDeactivated -> userListHasNoPronouns
     */
    public function testIndexPronounsDeactivatedUserListHasNoPronouns()
    {
        $this->user_with_pronoun = User::factory(['name' => 'x'])
            ->has(PersonalData::factory(['pronoun' => 'X']))->create();
        $this->user_without_pronoun = $this->user_b;

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $users = $data['users'];

                $this->assertEquals('x', $users[$this->user_with_pronoun->id]);
                $this->assertEquals('b', $users[$this->user_without_pronoun->id]);

                return $this->response;
            });

        $this->controller->index();
    }

    /**
     * @testdox index: pronounsActivated -> userListHasPronouns
     */
    public function testIndexPronounsActivatedUserListHasPronouns()
    {
        config(['enable_pronoun' => true]);

        $this->user_with_pronoun = User::factory(['name' => 'x'])
            ->has(PersonalData::factory(['pronoun' => 'X']))->create();
        $this->user_without_pronoun = $this->user_b;

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $users = $data['users'];

                $this->assertEquals('x (X)', $users[$this->user_with_pronoun->id]);
                $this->assertEquals('b', $users[$this->user_without_pronoun->id]);

                return $this->response;
            });

        $this->controller->index();
    }

    /**
     * @testdox index: withNoConversation -> returnsEmptyConversationList
     */
    public function testIndexWithNoConversationReturnsEmptyConversationList()
    {
        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $this->assertEquals(0, count($data['conversations']));
                return $this->response;
            });

        $this->controller->index();
    }

    /**
     * @testdox index: withConversation -> conversationContainsCorrectData
     */
    public function testIndexWithConversationConversationContainsCorrectData()
    {
        // save messages in wrong order to ensure latest message considers creation date, not id.
        $this->createMessage($this->user_a, $this->user_b, 'a>b', $this->now);
        $this->createMessage($this->user_b, $this->user_a, 'b>a', $this->two_minutes_ago);
        $this->createMessage($this->user_b, $this->user_a, 'b>a', $this->one_minute_ago);

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $conversations = $data['conversations'];

                $this->assertEquals(1, count($conversations));
                $c = $conversations[0];

                $this->assertArrayHasKey('other_user', $c);
                $this->assertArrayHasKey('latest_message', $c);
                $this->assertArrayHasKey('unread_messages', $c);

                $this->assertTrue($c['other_user'] instanceof User);
                $this->assertTrue($c['latest_message'] instanceof Message);
                $this->assertEquals('string', gettype($c['unread_messages']));

                $this->assertEquals('b', $c['other_user']->name);
                $this->assertEquals('b>a', $c['latest_message']->text);
                $this->assertEquals(2, $c['unread_messages']);

                return $this->response;
            });

        $this->controller->index();
    }

    /**
     * @testdox index: withConversations -> onlyContainsConversationsWithMe
     */
    public function testIndexWithConversationsOnlyContainsConversationsWithMe()
    {
        $user_c = User::factory(['name' => 'c'])->create();

        // save messages in wrong order to ensure latest message considers creation date, not id.
        $this->createMessage($this->user_a, $this->user_b, 'a>b', $this->now);
        $this->createMessage($this->user_b, $user_c, 'b>c', $this->now);
        $this->createMessage($user_c, $this->user_a, 'c>a', $this->now);

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $conversations = $data['conversations'];

                $this->assertEquals(2, count($conversations));
                $msg0 = $conversations[0]['latest_message']->text;
                $msg1 = $conversations[1]['latest_message']->text;
                $this->assertTrue(($msg0 == 'a>b' && $msg1 == 'c>a') || ($msg1 == 'c>a' && $msg0 == 'a>b'));

                return $this->response;
            });

        $this->controller->index();
    }

    /**
     * @testdox index: withConversations -> conversationsOrderedByDate
     */
    public function testIndexWithConversationsConversationsOrderedByDate()
    {
        $user_c = User::factory(['name' => 'c'])->create();
        $user_d = User::factory(['name' => 'd'])->create();

        $this->createMessage($this->user_a, $this->user_b, 'a>b', $this->now);
        $this->createMessage($user_d, $this->user_a, 'd>a', $this->two_minutes_ago);
        $this->createMessage($this->user_a, $user_c, 'a>c', $this->one_minute_ago);

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $conversations = $data['conversations'];

                $this->assertEquals('a>b', $conversations[0]['latest_message']->text);
                $this->assertEquals('a>c', $conversations[1]['latest_message']->text);
                $this->assertEquals('d>a', $conversations[2]['latest_message']->text);

                return $this->response;
            });

        $this->controller->index();
    }

    /**
     * @testdox ToConversation: withNoUserIdGiven -> throwsException
     */
    public function testToConversationWithNoUserIdGivenThrowsException()
    {
        $this->expectException(ValidationException::class);
        $this->controller->toConversation($this->request);
    }

    /**
     * @testdox ToConversation: withUserIdGiven -> redirect
     */
    public function testToConversationWithUserIdGivenRedirect()
    {
        $this->request = $this->request->withParsedBody([
            'user_id'  => '1',
        ]);
        $this->response->expects($this->once())
            ->method('redirectTo')
            ->with('http://localhost/messages/1')
            ->willReturn($this->response);

        $this->controller->toConversation($this->request);
    }

    /**
     * @testdox conversation: withNoUserIdGiven -> throwsException
     */
    public function testConversationWithNoUserIdGivenThrowsException()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->controller->conversation($this->request);
    }

    /**
     * @testdox conversation: withMyUserIdGiven -> throwsException
     */
    public function testConversationWithMyUserIdGivenThrowsException()
    {
        $this->request->attributes->set('user_id', $this->user_a->id);
        $this->expectException(HttpForbidden::class);
        $this->controller->conversation($this->request);
    }

    /**
     * @testdox conversation: withUnknownUserIdGiven -> throwsException
     */
    public function testConversationWithUnknownUserIdGivenThrowsException()
    {
        $this->request->attributes->set('user_id', '1234');
        $this->expectException(ModelNotFoundException::class);
        $this->controller->conversation($this->request);
    }

    /**
     * @testdox conversation: underNormalConditions -> returnsCorrectViewAndData
     */
    public function testConversationUnderNormalConditionsReturnsCorrectViewAndData()
    {
        $this->request->attributes->set('user_id', $this->user_b->id);

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $this->assertEquals('pages/messages/conversation.twig', $view);
                $this->assertArrayHasKey('messages', $data);
                $this->assertArrayHasKey('other_user', $data);
                $this->assertArrayOrCollection($data['messages']);
                $this->assertTrue($data['other_user'] instanceof User);
                return $this->response;
            });

        $this->controller->conversation($this->request);
    }

    /**
     * @testdox conversation: withNoMessages -> returnsEmptyMessageList
     */
    public function testConversationWithNoMessagesReturnsEmptyMessageList()
    {
        $this->request->attributes->set('user_id', $this->user_b->id);

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $this->assertEquals(0, count($data['messages']));
                return $this->response;
            });

        $this->controller->conversation($this->request);
    }

    /**
     * @testdox conversation: withMessages -> messagesOnlyWithThatUserOrderedByDate
     */
    public function testConversationWithMessagesMessagesOnlyWithThatUserOrderedByDate()
    {
        $this->request->attributes->set('user_id', $this->user_b->id);

        $user_c = User::factory(['name' => 'c'])->create();

        // to be listed
        $this->createMessage($this->user_a, $this->user_b, 'a>b', $this->now);
        $this->createMessage($this->user_b, $this->user_a, 'b>a', $this->two_minutes_ago);
        $this->createMessage($this->user_b, $this->user_a, 'b>a2', $this->one_minute_ago);

        // not to be listed
        $this->createMessage($this->user_a, $user_c, 'a>c', $this->now);
        $this->createMessage($user_c, $this->user_b, 'b>c', $this->now);

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $messages = $data['messages'];
                $this->assertEquals(3, count($messages));
                $this->assertEquals('b>a', $messages[0]->text);
                $this->assertEquals('b>a2', $messages[1]->text);
                $this->assertEquals('a>b', $messages[2]->text);

                return $this->response;
            });

        $this->controller->conversation($this->request);
    }

    /**
     * @testdox conversation: withUnreadMessages -> messagesToMeWillStillBeReturnedAsUnread
     */
    public function testConversationWithUnreadMessagesMessagesToMeWillStillBeReturnedAsUnread()
    {
        $this->request->attributes->set('user_id', $this->user_b->id);
        $this->createMessage($this->user_b, $this->user_a, 'b>a', $this->now);

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $this->assertFalse($data['messages'][0]->read);
                return $this->response;
            });
        $this->controller->conversation($this->request);
    }

    /**
     * @testdox conversation: withUnreadMessages -> messagesToMeWillBeMarkedAsRead
     */
    public function testConversationWithUnreadMessagesMessagesToMeWillBeMarkedAsRead()
    {
        $this->request->attributes->set('user_id', $this->user_b->id);
        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                return $this->response;
            });

        $msg = $this->createMessage($this->user_b, $this->user_a, 'b>a', $this->now);
        $this->controller->conversation($this->request);
        $this->assertTrue(Message::whereId($msg->id)->first()->read);
    }

    /**
     * @testdox send: withNoTextGiven -> throwsException
     */
    public function testSendWithNoTextGivenThrowsException()
    {
        $this->expectException(ValidationException::class);
        $this->controller->send($this->request);
    }

    /**
     * @testdox send: withNoUserIdGiven -> throwsException
     */
    public function testSendWithNoUserIdGivenThrowsException()
    {
        $this->request = $this->request->withParsedBody([
            'text'  => 'a',
        ]);
        $this->expectException(ModelNotFoundException::class);
        $this->controller->send($this->request);
    }

    /**
     * @testdox send: withMyUserIdGiven -> throwsException
     */
    public function testSendWithMyUserIdGivenThrowsException()
    {
        $this->request = $this->request->withParsedBody([
            'text'  => 'a',
        ]);
        $this->request->attributes->set('user_id', $this->user_a->id);
        $this->expectException(HttpForbidden::class);
        $this->controller->send($this->request);
    }

    /**
     * @testdox send: withUnknownUserIdGiven -> throwsException
     */
    public function testSendWithUnknownUserIdGivenThrowsException()
    {
        $this->request = $this->request->withParsedBody([
            'text'  => 'a',
        ]);
        $this->request->attributes->set('user_id', '1234');
        $this->expectException(ModelNotFoundException::class);
        $this->controller->send($this->request);
    }

    /**
     * @testdox send: withUserAndTextGiven -> savesMessage
     */
    public function testSendWithUserAndTextGivenSavesMessage()
    {
        $this->request = $this->request->withParsedBody([
            'text'  => 'a',
        ]);
        $this->request->attributes->set('user_id', $this->user_b->id);

        $this->response->expects($this->once())
            ->method('redirectTo')
            ->with('http://localhost/messages/' . $this->user_b->id)
            ->willReturn($this->response);

        $this->controller->send($this->request);

        $msg = Message::whereText('a')->first();
        $this->assertEquals($this->user_a->id, $msg->user_id);
        $this->assertEquals($this->user_b->id, $msg->receiver_id);
        $this->assertFalse($msg->read);
    }

    /**
     * @testdox delete: withNoMsgIdGiven -> throwsException
     */
    public function testDeleteWithNoMsgIdGivenThrowsException()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->controller->delete($this->request);
    }

    /**
     * @testdox delete: tryingToDeleteSomeonesMessage -> throwsException
     */
    public function testDeleteTryingToDeleteSomeonesMessageThrowsException()
    {
        $this->expectException(HttpForbidden::class);

        $msg = $this->createMessage($this->user_b, $this->user_a, 'a>b', $this->now);
        $this->request->attributes->set('msg_id', $msg->id);

        $this->controller->delete($this->request);
    }

    /**
     * @testdox delete: tryingToDeleteMyMessage -> deletesItAndRedirect
     */
    public function testDeleteTryingToDeleteMyMessageDeletesItAndRedirect()
    {
        $msg = $this->createMessage($this->user_a, $this->user_b, 'a>b', $this->now);
        $this->request->attributes->set('msg_id', $msg->id);
        $this->request->attributes->set('user_id', '1');

        $this->response->expects($this->once())
            ->method('redirectTo')
            ->with('http://localhost/messages/1')
            ->willReturn($this->response);

        $this->controller->delete($this->request);

        $this->assertEquals(0, count(Message::whereId($msg->id)->get()));
    }

    /**
     * @testdox NumberOfUnreadMessages: withNoMessages -> returns0
     */
    public function testNumberOfUnreadMessagesWithNoMessagesReturns0()
    {
        $this->assertEquals(0, $this->controller->numberOfUnreadMessages());
    }

    /**
     * @testdox NumberOfUnreadMessages: withMessagesNotToMe -> messagesNotToMeAreIgnored
     */
    public function testNumberOfUnreadMessagesWithMessagesNotToMeMessagesNotToMeAreIgnored()
    {
        $user_c = User::factory(['name' => 'c'])->create();

        $this->createMessage($this->user_a, $this->user_b, 'a>b', $this->now);
        $this->createMessage($this->user_b, $user_c, 'b>c', $this->now);
        $this->assertEquals(0, $this->controller->numberOfUnreadMessages());
    }

    /**
     * @testdox NumberOfUnreadMessages: withMessages -> returnsSumOfUnreadMessagesSentToMe
     */
    public function testNumberOfUnreadMessagesWithMessagesReturnsSumOfUnreadMessagesSentToMe()
    {
        $user_c = User::factory(['name' => 'c'])->create();

        $this->createMessage($this->user_b, $this->user_a, 'b>a1', $this->now);
        $this->createMessage($this->user_b, $this->user_a, 'b>a2', $this->now);
        $this->createMessage($user_c, $this->user_a, 'c>a', $this->now);

        $this->assertEquals(3, $this->controller->numberOfUnreadMessages());
    }

    /**
     * Setup environment
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->auth = $this->createMock(Authenticator::class);
        $this->app->instance(Authenticator::class, $this->auth);

        $this->app->bind(UrlGeneratorInterface::class, UrlGenerator::class);

        $this->user_a = User::factory(['name' => 'a'])->create();
        $this->user_b = User::factory(['name' => 'b'])->create();
        $this->setExpects($this->auth, 'user', null, $this->user_a, $this->any());

        $this->now = Carbon::now();
        $this->one_minute_ago = Carbon::now()->subMinute();
        $this->two_minutes_ago = Carbon::now()->subMinutes(2);

        $this->controller = $this->app->get(MessagesController::class);
        $this->controller->setValidator(new Validator());
    }

    protected function assertArrayOrCollection($obj)
    {
        $this->assertTrue(gettype($obj) == 'array' || $obj instanceof Collection);
    }

    protected function createMessage(User $from, User $to, string $text, Carbon $at): Message
    {
        Message::unguard(); // unguard temporarily to save custom creation dates.
        $msg = new Message([
            'user_id' => $from->id,
            'receiver_id' => $to->id,
            'text' => $text,
            'created_at' => $at,
            'updated_at' => $at,
        ]);
        $msg->save();
        Message::reguard();

        return $msg;
    }
}
