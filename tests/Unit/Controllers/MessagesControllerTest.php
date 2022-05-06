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
    /** @var User */
    protected $user_c;

    /** @var Carbon */
    protected $now;
    /** @var Carbon */
    protected $one_minute_ago;
    /** @var Carbon */
    protected $two_minutes_ago;

    public function testIndex_underNormalConditions_returnsCorrectViewAndData()
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

    public function testIndex_otherUsersExist_returnsUsersWithoutMeOrderedByName()
    {
        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $users = $data['users'];

                $this->assertEquals(2, count($users));
                $this->assertEquals('b', $users[$this->user_b->id]);
                $this->assertEquals('c', $users[$this->user_c->id]);

                return $this->response;
            });

        $this->controller->index();
    }

    public function testIndex_whenPronounsDeactivated_userListHasNoPronouns()
    {
        $this->user_with_pronoun = User::factory(['name' => 'x'])
            ->has(PersonalData::factory(['pronoun' => 'X']))->create();
        $this->user_without_pronoun = User::factory(['name' => 'y'])->create();

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $users = $data['users'];

                $this->assertEquals('x', $users[$this->user_with_pronoun->id]);
                $this->assertEquals('y', $users[$this->user_without_pronoun->id]);

                return $this->response;
            });

        $this->controller->index();
    }

    public function testIndex_whenPronounsActivated_userListHasPronouns()
    {
        config(['enable_pronoun' => true]);

        $this->user_with_pronoun = User::factory(['name' => 'x'])
            ->has(PersonalData::factory(['pronoun' => 'X']))->create();
        $this->user_without_pronoun = User::factory(['name' => 'y'])->create();

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $users = $data['users'];

                $this->assertEquals('x (X)', $users[$this->user_with_pronoun->id]);
                $this->assertEquals('y', $users[$this->user_without_pronoun->id]);

                return $this->response;
            });

        $this->controller->index();
    }

    public function testIndex_withNoConversation_returnsEmptyConversationList()
    {
        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $this->assertEquals(0, count($data['conversations']));
                return $this->response;
            });

        $this->controller->index();
    }

    public function testIndex_withConversation_conversationContainsOtherUserAndLatestMessageAndUnreadCount()
    {
        // save messages in wrong order to ensure latest message considers creation date, not id.
        $this->create_message($this->user_a, $this->user_b, 'a>b', $this->now);
        $this->create_message($this->user_b, $this->user_a, 'b>a', $this->two_minutes_ago);
        $this->create_message($this->user_b, $this->user_a, 'b>a', $this->one_minute_ago);

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

    public function testIndex_withConversations_onlyContainsConversationsWithMe()
    {
        // save messages in wrong order to ensure latest message considers creation date, not id.
        $this->create_message($this->user_a, $this->user_b, 'a>b', $this->now);
        $this->create_message($this->user_b, $this->user_c, 'b>c', $this->now);
        $this->create_message($this->user_c, $this->user_a, 'c>a', $this->now);

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

    public function testIndex_withConversations_conversationsOrderedByDate()
    {
        $user_d = User::factory(['name' => 'd'])->create();

        $this->create_message($this->user_a, $this->user_b, 'a>b', $this->now);
        $this->create_message($user_d, $this->user_a, 'd>a', $this->two_minutes_ago);
        $this->create_message($this->user_a, $this->user_c, 'a>c', $this->one_minute_ago);

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

    public function testToConversation_withNoUserIdGiven_throwsException() {
        $this->expectException(ValidationException::class);
        $this->controller->setValidator(new Validator());
        $this->controller->to_conversation($this->request);
    }

    public function testToConversation_withUserIdGiven_redirect() {
        $this->request = $this->request->withParsedBody([
            'user_id'  => '1',
        ]);
        $this->response->expects($this->once())
            ->method('redirectTo')
            ->with('http://localhost/messages/1')
            ->willReturn($this->response);

        $this->controller->setValidator(new Validator());
        $this->controller->to_conversation($this->request);
    }

    public function testConversation_withNoUserIdGiven_throwsException() {
        $this->expectException(ModelNotFoundException::class);
        $this->controller->conversation($this->request);
    }

    public function testConversation_withMyUserIdGiven_throwsException() {
        $this->request->attributes->set('user_id', $this->user_a->id);
        $this->expectException(HttpForbidden::class);
        $this->controller->conversation($this->request);
    }

    public function testConversation_withUnknownUserIdGiven_throwsException() {
        $this->request->attributes->set('user_id', '1234');
        $this->expectException(ModelNotFoundException::class);
        $this->controller->conversation($this->request);
    }

    public function testConversation_underNormalConditions_returnsCorrectViewAndData() {
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

    public function testConversation_withNoMessages_returnsEmptyMessageList() {
        $this->request->attributes->set('user_id', $this->user_b->id);

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $this->assertEquals(0, count($data['messages']));
                return $this->response;
            });

        $this->controller->conversation($this->request);
    }

    public function testConversation_withMessages_messagesOnlyWithThatUserOrderedByDate() {
        $this->request->attributes->set('user_id', $this->user_b->id);

        // to be listed
        $this->create_message($this->user_a, $this->user_b, 'a>b', $this->now);
        $this->create_message($this->user_b, $this->user_a, 'b>a', $this->two_minutes_ago);
        $this->create_message($this->user_b, $this->user_a, 'b>a2', $this->one_minute_ago);

        // not to be listed
        $this->create_message($this->user_a, $this->user_c, 'a>c', $this->now);
        $this->create_message($this->user_c, $this->user_b, 'b>c', $this->now);

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

    public function testConversation_withUnreadMessages_messagesToMeWillStillBeReturnedAsUnread() {
        $this->request->attributes->set('user_id', $this->user_b->id);
        $this->create_message($this->user_b, $this->user_a, 'b>a', $this->now);

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $this->assertFalse($data['messages'][0]->read);
                return $this->response;
            });
        $this->controller->conversation($this->request);

    }

    public function testConversation_withUnreadMessages_messagesToMeWillBeMarkedAsRead() {
        $this->request->attributes->set('user_id', $this->user_b->id);
        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) { return $this->response; });

        $msg = $this->create_message($this->user_b, $this->user_a, 'b>a', $this->now);
        $this->controller->conversation($this->request);
        $this->assertTrue(Message::whereId($msg->id)->first()->read);
    }

    public function testSend_withNoTextGiven_throwsException() {
        $this->expectException(ValidationException::class);
        $this->controller->setValidator(new Validator());
        $this->controller->send($this->request);
    }

    public function testSend_withNoUserIdGiven_throwsException() {
        $this->request = $this->request->withParsedBody([
            'text'  => 'a',
        ]);
        $this->expectException(ModelNotFoundException::class);
        $this->controller->setValidator(new Validator());
        $this->controller->send($this->request);
    }

    public function testSend_withMyUserIdGiven_throwsException() {
        $this->request = $this->request->withParsedBody([
            'text'  => 'a',
        ]);
        $this->request->attributes->set('user_id', $this->user_a->id);
        $this->expectException(HttpForbidden::class);
        $this->controller->setValidator(new Validator());
        $this->controller->send($this->request);
    }

    public function testSend_withUnknownUserIdGiven_throwsException() {
        $this->request = $this->request->withParsedBody([
            'text'  => 'a',
        ]);
        $this->request->attributes->set('user_id', '1234');
        $this->expectException(ModelNotFoundException::class);
        $this->controller->setValidator(new Validator());
        $this->controller->send($this->request);
    }

    public function testSend_withUserAndTextGiven_savesMessage() {
        $this->request = $this->request->withParsedBody([
            'text'  => 'a',
        ]);
        $this->request->attributes->set('user_id', $this->user_b->id);
        $this->controller->setValidator(new Validator());

        $this->response->expects($this->once())
            ->method('redirectTo')
            ->with('http://localhost/messages/'. $this->user_b->id)
            ->willReturn($this->response);

        $this->controller->send($this->request);

        $msg = Message::whereText('a')->first();
        $this->assertEquals($this->user_a->id, $msg->user_id);
        $this->assertEquals($this->user_b->id, $msg->receiver_id);
        $this->assertFalse($msg->read);
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
        $this->user_c = User::factory(['name' => 'c'])->create();
        $this->setExpects($this->auth, 'user', null, $this->user_a, $this->any());

        $this->now = Carbon::now();
        $this->one_minute_ago = Carbon::now()->subMinute();
        $this->two_minutes_ago = Carbon::now()->subMinutes(2);

        $this->controller = $this->app->get(MessagesController::class);
    }

    protected function assertArrayOrCollection($obj)
    {
        $this->assertTrue(gettype($obj) == 'array' || $obj instanceof Collection);
    }

    protected function create_message(User $from, User $to, string $text, Carbon $at): Message
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
