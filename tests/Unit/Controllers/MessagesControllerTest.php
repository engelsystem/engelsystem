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
    /** @var User */
    protected $user_d;

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

                $this->assertEquals(3, count($users));
                $this->assertEquals('b', $users[$this->user_b->id]);
                $this->assertEquals('c', $users[$this->user_c->id]);
                $this->assertEquals('d', $users[$this->user_d->id]);

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
        $this->create_message($this->user_a, $this->user_b, "a>b", $this->now);
        $this->create_message($this->user_b, $this->user_a, "b>a", $this->two_minutes_ago);
        $this->create_message($this->user_b, $this->user_a, "b>a", $this->one_minute_ago);

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $conversations = $data['conversations'];

                $this->assertEquals(1, count($conversations));
                $c = $conversations[0];

                $this->assertArrayHasKey('other_user', $c);
                $this->assertArrayHasKey('latest_message', $c);
                $this->assertArrayHasKey('unread_messages', $c);

                $this->assertEquals('User', class_basename($c['other_user']));
                $this->assertEquals('Message', class_basename($c['latest_message']));
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
        $this->create_message($this->user_a, $this->user_b, "a>b", $this->now);
        $this->create_message($this->user_b, $this->user_c, "b>c", $this->now);
        $this->create_message($this->user_c, $this->user_a, "c>a", $this->now);

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
        $this->create_message($this->user_a, $this->user_b, "a>b", $this->now);
        $this->create_message($this->user_d, $this->user_a, "d>a", $this->two_minutes_ago);
        $this->create_message($this->user_a, $this->user_c, "a>c", $this->one_minute_ago);

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
        $this->user_d = User::factory(['name' => 'd'])->create();
        $this->setExpects($this->auth, 'user', null, $this->user_a, $this->any());

        $this->now = Carbon::now();
        $this->one_minute_ago = Carbon::now()->subMinute();
        $this->two_minutes_ago = Carbon::now()->subMinutes(2);

        $this->controller = $this->app->get(MessagesController::class);
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
