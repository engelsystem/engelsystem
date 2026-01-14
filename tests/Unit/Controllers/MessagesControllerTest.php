<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers;

use Carbon\Carbon;
use Engelsystem\Controllers\MessagesController;
use Engelsystem\Events\EventDispatcher;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Exceptions\HttpForbidden;
use Engelsystem\Http\Exceptions\ValidationException;
use Engelsystem\Http\UrlGenerator;
use Engelsystem\Http\UrlGeneratorInterface;
use Engelsystem\Http\Validation\Validator;
use Engelsystem\Models\Message;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\HasDatabase;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversMethod(MessagesController::class, '__construct')]
#[CoversMethod(MessagesController::class, 'index')]
#[CoversMethod(MessagesController::class, 'listConversations')]
#[CoversMethod(MessagesController::class, 'latestMessagePerConversation')]
#[CoversMethod(MessagesController::class, 'numberOfUnreadMessagesPerConversation')]
#[CoversMethod(MessagesController::class, 'raw')]
#[CoversMethod(MessagesController::class, 'redirectToConversation')]
#[CoversMethod(MessagesController::class, 'messagesOfConversation')]
#[CoversMethod(MessagesController::class, 'send')]
#[CoversMethod(MessagesController::class, 'delete')]
#[AllowMockObjectsWithoutExpectations]
class MessagesControllerTest extends ControllerTestCase
{
    use HasDatabase;

    protected MessagesController $controller;

    protected Authenticator&MockObject $auth;

    protected User $userA;
    protected User $userB;

    protected Carbon $now;
    protected Carbon $oneMinuteAgo;
    protected Carbon $twoMinutesAgo;

    protected EventDispatcher $events;

    #[TestDox('index: underNormalConditions -> returnsCorrectViewAndData')]
    public function testIndexUnderNormalConditionsReturnsCorrectViewAndData(): void
    {
        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $this->assertEquals('pages/messages/index.twig', $view);
                $this->assertArrayHasKey('conversations', $data);
                $this->assertArrayHasKey('users', $data);
                $this->assertArrayOrCollection($data['conversations']);
                $this->assertArrayOrCollection($data['users']);
                return $this->response;
            });

        $this->controller->index();
    }

    #[TestDox('index: User is shown as first name and last name instead of nickname')]
    public function testIndexUnderNormalConditionsReturnsFormattedUserName(): void
    {
        $this->config->set('display_full_name', true);

        $this->userA->personalData->first_name = 'Frank';
        $this->userA->personalData->last_name = 'Nord';
        $this->userA->personalData->save();

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $this->assertEquals('Frank Nord', $data['users'][1]);
                return $this->response;
            });

        $this->controller->index();
    }

    #[TestDox('index: usersExist -> returnsUsersWithMeAtFirstPosition')]
    public function testIndexUsersExistReturnsUsersWithMeAtFirstPosition(): void
    {
        User::factory(['name' => '0'])->create(); // alphabetically before me ("a"), but still listed after me

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $users = $data['users'];

                $this->assertCount(3, $users);
                $this->assertEquals('a', $users->shift());
                $this->assertEquals('0', $users->shift());
                $this->assertEquals('b', $users->shift());

                return $this->response;
            });

        $this->controller->index();
    }

    #[TestDox('index: withNoConversation -> returnsEmptyConversationList')]
    public function testIndexWithNoConversationReturnsEmptyConversationList(): void
    {
        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $this->assertCount(0, $data['conversations']);
                return $this->response;
            });

        $this->controller->index();
    }

    #[TestDox('index: withConversation -> conversationContainsCorrectData')]
    public function testIndexWithConversationConversationContainsCorrectData(): void
    {
        // save messages in wrong order to ensure latest message considers creation date, not id.
        $this->createMessage($this->userA, $this->userB, 'a>b', $this->now);
        $this->createMessage($this->userB, $this->userA, 'b>a', $this->twoMinutesAgo);
        $this->createMessage($this->userB, $this->userA, 'b>a', $this->oneMinuteAgo);

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $conversations = $data['conversations'];

                $this->assertCount(1, $conversations);
                $c = $conversations[0];

                $this->assertArrayHasKey('other_user', $c);
                $this->assertArrayHasKey('latest_message', $c);
                $this->assertArrayHasKey('unread_messages', $c);

                $this->assertTrue($c['other_user'] instanceof User);
                $this->assertTrue($c['latest_message'] instanceof Message);
                $this->assertIsNumeric($c['unread_messages']);

                $this->assertEquals('b', $c['other_user']->name);
                $this->assertEquals('b>a', $c['latest_message']->text);
                $this->assertEquals(2, $c['unread_messages']);

                return $this->response;
            });

        $this->controller->index();
    }

    #[TestDox('index: withConversations -> onlyContainsConversationsWithMe')]
    public function testIndexWithConversationsOnlyContainsConversationsWithMe(): void
    {
        $userC = User::factory(['name' => 'c'])->create();

        // save messages in wrong order to ensure latest message considers creation date, not id.
        $this->createMessage($this->userA, $this->userB, 'a>b', $this->now);
        $this->createMessage($this->userB, $userC, 'b>c', $this->now);
        $this->createMessage($userC, $this->userA, 'c>a', $this->now);

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $conversations = $data['conversations'];

                $this->assertCount(2, $conversations);
                $msg0 = $conversations[0]['latest_message']->text;
                $msg1 = $conversations[1]['latest_message']->text;
                $this->assertTrue(($msg0 == 'a>b' && $msg1 == 'c>a') || ($msg1 == 'c>a' && $msg0 == 'a>b'));

                return $this->response;
            });

        $this->controller->index();
    }

    #[TestDox('index: withConversations -> conversationsOrderedByDate')]
    public function testIndexWithConversationsConversationsOrderedByDate(): void
    {
        $userC = User::factory(['name' => 'c'])->create();
        $userD = User::factory(['name' => 'd'])->create();

        $this->createMessage($this->userA, $this->userB, 'a>b', $this->now);
        $this->createMessage($userD, $this->userA, 'd>a', $this->twoMinutesAgo);
        $this->createMessage($this->userA, $userC, 'a>c', $this->oneMinuteAgo);

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

    #[TestDox('redirectToConversation: withNoUserIdGiven -> throwsException')]
    public function testRedirectToConversationWithNoUserIdGivenThrowsException(): void
    {
        $this->expectException(ValidationException::class);
        $this->controller->redirectToConversation($this->request);
    }

    #[TestDox('redirectToConversation: withUserIdGiven -> redirect')]
    public function testRedirectToConversationWithUserIdGivenRedirect(): void
    {
        $this->request = $this->request->withParsedBody(['user_id' => '1']);
        $this->response->expects($this->once())
            ->method('redirectTo')
            ->with('http://localhost/messages/1#newest')
            ->willReturn($this->response);

        $this->controller->redirectToConversation($this->request);
    }

    #[TestDox('messagesOfConversation: withNoUserIdGiven -> throwsException')]
    public function testMessagesOfConversationWithNoUserIdGivenThrowsException(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->controller->messagesOfConversation($this->request);
    }

    #[TestDox('messagesOfConversation: withUnknownUserIdGiven -> throwsException')]
    public function testMessagesOfConversationWithUnknownUserIdGivenThrowsException(): void
    {
        $this->request->attributes->set('user_id', '1234');
        $this->expectException(ModelNotFoundException::class);
        $this->controller->messagesOfConversation($this->request);
    }

    #[TestDox('messagesOfConversation: underNormalConditions -> returnsCorrectViewAndData')]
    public function testMessagesOfConversationUnderNormalConditionsReturnsCorrectViewAndData(): void
    {
        $this->request->attributes->set('user_id', $this->userB->id);

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

        $this->controller->messagesOfConversation($this->request);
    }

    #[TestDox('messagesOfConversation: withNoMessages -> returnsEmptyMessageList')]
    public function testMessagesOfConversationWithNoMessagesReturnsEmptyMessageList(): void
    {
        $this->request->attributes->set('user_id', $this->userB->id);

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $this->assertCount(0, $data['messages']);
                return $this->response;
            });

        $this->controller->messagesOfConversation($this->request);
    }

    #[TestDox('messagesOfConversation: withMessages -> messagesOnlyWithThatUserOrderedByDate')]
    public function testMessagesOfConversationWithMessagesMessagesOnlyWithThatUserOrderedByDate(): void
    {
        $this->request->attributes->set('user_id', $this->userB->id);

        $userC = User::factory(['name' => 'c'])->create();

        // to be listed
        $this->createMessage($this->userA, $this->userB, 'a>b', $this->now);
        $this->createMessage($this->userB, $this->userA, 'b>a', $this->twoMinutesAgo);
        $this->createMessage($this->userB, $this->userA, 'b>a2', $this->oneMinuteAgo);

        // not to be listed
        $this->createMessage($this->userA, $userC, 'a>c', $this->now);
        $this->createMessage($userC, $this->userB, 'b>c', $this->now);

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $messages = $data['messages'];
                $this->assertCount(3, $messages);
                $this->assertEquals('b>a', $messages[0]->text);
                $this->assertEquals('b>a2', $messages[1]->text);
                $this->assertEquals('a>b', $messages[2]->text);

                return $this->response;
            });

        $this->controller->messagesOfConversation($this->request);
    }

    #[TestDox('messagesOfConversation: withUnreadMessages -> messagesToMeWillStillBeReturnedAsUnread')]
    public function testMessagesOfConversationWithUnreadMessagesMessagesToMeWillStillBeReturnedAsUnread(): void
    {
        $this->request->attributes->set('user_id', $this->userB->id);
        $this->createMessage($this->userB, $this->userA, 'b>a', $this->now);

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $this->assertFalse($data['messages'][0]->read);
                return $this->response;
            });
        $this->controller->messagesOfConversation($this->request);
    }

    #[TestDox('messagesOfConversation: withUnreadMessages -> messagesToMeWillBeMarkedAsRead')]
    public function testMessagesOfConversationWithUnreadMessagesMessagesToMeWillBeMarkedAsRead(): void
    {
        $this->request->attributes->set('user_id', $this->userB->id);
        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                return $this->response;
            });

        $msg = $this->createMessage($this->userB, $this->userA, 'b>a', $this->now);
        $this->controller->messagesOfConversation($this->request);
        $this->assertTrue(Message::whereId($msg->id)->first()->read);
    }

    #[TestDox('messagesOfConversation: withMyUserIdGiven -> returnsMessagesFromMeToMe')]
    public function testMessagesOfConversationWithMyUserIdGivenReturnsMessagesFromMeToMe(): void
    {
        $this->request->attributes->set('user_id', $this->userA->id); // myself

        $this->createMessage($this->userA, $this->userA, 'a>a1', $this->now);
        $this->createMessage($this->userA, $this->userA, 'a>a2', $this->twoMinutesAgo);

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $messages = $data['messages'];
                $this->assertCount(2, $messages);
                $this->assertEquals('a>a2', $messages[0]->text);
                $this->assertEquals('a>a1', $messages[1]->text);

                return $this->response;
            });

        $this->controller->messagesOfConversation($this->request);
    }

    #[TestDox('send: withNoTextGiven -> throwsException')]
    public function testSendWithNoTextGivenThrowsException(): void
    {
        $this->expectException(ValidationException::class);
        $this->controller->send($this->request);
    }

    #[TestDox('send: withNoUserIdGiven -> throwsException')]
    public function testSendWithNoUserIdGivenThrowsException(): void
    {
        $this->request = $this->request->withParsedBody(['text' => 'a']);
        $this->expectException(ModelNotFoundException::class);
        $this->controller->send($this->request);
    }

    #[TestDox('send: withUnknownUserIdGiven -> throwsException')]
    public function testSendWithUnknownUserIdGivenThrowsException(): void
    {
        $this->request = $this->request->withParsedBody(['text' => 'a']);
        $this->request->attributes->set('user_id', '1234');
        $this->expectException(ModelNotFoundException::class);
        $this->controller->send($this->request);
    }

    #[TestDox('send: withUserAndTextGiven -> savesMessage')]
    public function testSendWithUserAndTextGivenSavesMessage(): void
    {
        $this->request = $this->request->withParsedBody(['text' => 'a']);
        $this->request->attributes->set('user_id', $this->userB->id);

        $this->response->expects($this->once())
            ->method('redirectTo')
            ->with('http://localhost/messages/' . $this->userB->id . '#newest')
            ->willReturn($this->response);

        $this->setExpects($this->events, 'dispatch', ['message.created'], []);

        $this->controller->send($this->request);

        $msg = Message::whereText('a')->first();
        $this->assertEquals($this->userA->id, $msg->user_id);
        $this->assertEquals($this->userB->id, $msg->receiver_id);
        $this->assertFalse($msg->read);
    }

    #[TestDox('send: withMyUserIdGiven -> savesMessageAlreadyMarkedAsRead')]
    public function testSendWithMyUserIdGivenSavesMessageAlreadyMarkedAsRead(): void
    {
        $this->request = $this->request->withParsedBody(['text' => 'a']);
        $this->request->attributes->set('user_id', $this->userA->id);

        $this->response->expects($this->once())
            ->method('redirectTo')
            ->with('http://localhost/messages/' . $this->userA->id . '#newest')
            ->willReturn($this->response);

        $this->setExpects($this->events, 'dispatch', ['message.created'], []);

        $this->controller->send($this->request);

        $msg = Message::whereText('a')->first();
        $this->assertEquals($this->userA->id, $msg->user_id);
        $this->assertEquals($this->userA->id, $msg->receiver_id);
        $this->assertTrue($msg->read);
    }

    #[TestDox('delete: withNoMsgIdGiven -> throwsException')]
    public function testDeleteWithNoMsgIdGivenThrowsException(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->controller->delete($this->request);
    }

    #[TestDox('delete: tryingToDeleteSomeonesMessage -> throwsException')]
    public function testDeleteTryingToDeleteSomeonesMessageThrowsException(): void
    {
        $msg = $this->createMessage($this->userB, $this->userA, 'a>b', $this->now);
        $this->request->attributes->set('msg_id', $msg->id);
        $this->expectException(HttpForbidden::class);

        $this->controller->delete($this->request);
    }

    #[TestDox('delete: tryingToDeleteMyMessage -> deletesItAndRedirect')]
    public function testDeleteTryingToDeleteMyMessageDeletesItAndRedirect(): void
    {
        $msg = $this->createMessage($this->userA, $this->userB, 'a>b', $this->now);
        $this->request->attributes->set('msg_id', $msg->id);
        $this->request->attributes->set('user_id', '1');

        $this->response->expects($this->once())
            ->method('redirectTo')
            ->with('http://localhost/messages/1#newest')
            ->willReturn($this->response);

        $this->controller->delete($this->request);

        $this->assertCount(0, Message::whereId($msg->id)->get());
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

        $this->userA = User::factory(['name' => 'a'])->create();
        $this->userB = User::factory(['name' => 'b'])->create();
        $this->setExpects($this->auth, 'user', null, $this->userA, $this->any());

        $this->now = Carbon::now();
        $this->oneMinuteAgo = Carbon::now()->subMinute();
        $this->twoMinutesAgo = Carbon::now()->subMinutes(2);

        $this->controller = $this->app->get(MessagesController::class);
        $this->controller->setValidator(new Validator());

        $this->events = $this->createMock(EventDispatcher::class);
        $this->app->instance('events.dispatcher', $this->events);
    }

    protected function assertArrayOrCollection(mixed $obj): void
    {
        $this->assertTrue(gettype($obj) == 'array' || $obj instanceof Collection);
    }

    protected function createMessage(User $from, User $to, string $text, Carbon $at): Message
    {
        Message::unguard(); // unguard temporarily to save custom creation dates.
        $msg = new Message([
            'user_id'     => $from->id,
            'receiver_id' => $to->id,
            'text'        => $text,
            'created_at'  => $at,
            'updated_at'  => $at,
        ]);
        $msg->save();
        Message::reguard();

        return $msg;
    }
}
