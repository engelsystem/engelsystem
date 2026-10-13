<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Api;

use Engelsystem\Controllers\Api\MessagesController;
use Engelsystem\Controllers\Api\Resources\MessageResource;
use Engelsystem\Events\EventDispatcher;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Exceptions\HttpForbidden;
use Engelsystem\Http\Exceptions\ValidationException;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Http\Validation\Validator;
use Engelsystem\Models\Message;
use Engelsystem\Models\User\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversMethod(MessagesController::class, 'index')]
#[CoversMethod(MessagesController::class, 'show')]
#[CoversMethod(MessagesController::class, 'send')]
#[CoversMethod(MessagesController::class, 'delete')]
#[CoversMethod(MessagesController::class, 'setRead')]
#[CoversMethod(MessagesController::class, 'hasPermission')]
#[CoversMethod(MessagesController::class, 'webPermission')]
#[CoversMethod(MessageResource::class, 'toArray')]
#[AllowMockObjectsWithoutExpectations]
class MessagesControllerTest extends ApiBaseControllerTestCase
{
    protected EventDispatcher&MockObject $events;

    public function setUp(): void
    {
        parent::setUp();

        // Message::sendMessage() fires message.created - bind a (loose) mock so it resolves
        // for every test that creates fixture messages, not just the ones asserting on it.
        $this->events = $this->createMock(EventDispatcher::class);
        $this->app->instance('events.dispatcher', $this->events);
    }

    protected function controllerWithAuth(User $currentUser): MessagesController
    {
        $auth = $this->createMock(Authenticator::class);
        $this->setExpects($auth, 'user', null, $currentUser, $this->any());

        $controller = new MessagesController(new Response());
        $controller->setAuth($auth);
        $controller->setValidator(new Validator());

        return $controller;
    }

    public function testIndex(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        Message::sendMessage($userA, $userB, 'hi');

        $controller = $this->controllerWithAuth($userA);

        $response = $controller->index();
        $this->validateApiResponse('/messages', 'get', $response);

        $this->assertEquals(['application/json'], $response->getHeader('content-type'));
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertCount(1, $data['data']);
        $this->assertEquals($userB->id, $data['data'][0]['other_user']['id']);
        $this->assertEquals('hi', $data['data'][0]['latest_message']['text']);
    }

    public function testIndexUnreadCount(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        Message::sendMessage($userB, $userA, 'unread 1');
        Message::sendMessage($userB, $userA, 'unread 2');

        $controller = $this->controllerWithAuth($userA);

        $response = $controller->index();
        $data = json_decode($response->getContent(), true);

        $this->assertEquals(2, $data['data'][0]['unread_messages']);
    }

    public function testShow(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        Message::sendMessage($userA, $userB, 'first');
        Message::sendMessage($userB, $userA, 'second');

        $controller = $this->controllerWithAuth($userA);
        $request = new Request();
        $request = $request->withAttribute('user_id', (string) $userB->id);

        $response = $controller->show($request);
        $this->validateApiResponse('/messages/{id}', 'get', $response);

        $data = json_decode($response->getContent(), true);
        $this->assertEquals($userB->id, $data['data']['other_user']['id']);
        $this->assertCount(2, $data['data']['messages']);
        $this->assertEquals('first', $data['data']['messages'][0]['text']);
        $this->assertEquals('second', $data['data']['messages'][1]['text']);
    }

    public function testShowDoesNotMarkMessagesAsRead(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $message = Message::sendMessage($userB, $userA, 'unread');
        $this->assertFalse($message->read);

        $controller = $this->controllerWithAuth($userA);
        $request = new Request();
        $request = $request->withAttribute('user_id', (string) $userB->id);

        $controller->show($request);

        $this->assertFalse(Message::find($message->id)->read);
    }

    public function testShowNotFound(): void
    {
        $userA = User::factory()->create();
        $controller = $this->controllerWithAuth($userA);

        $request = new Request();
        $request = $request->withAttribute('user_id', '999999');

        $this->expectException(ModelNotFoundException::class);
        $controller->show($request);
    }

    public function testSend(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $this->setExpects($this->events, 'dispatch', ['message.created'], []);

        $controller = $this->controllerWithAuth($userA);
        $request = new Request();
        $request = $request->withAttribute('user_id', (string) $userB->id);
        $request = $request->withParsedBody(['text' => 'hello there']);

        $response = $controller->send($request);
        $this->validateApiResponse('/messages/{id}', 'post', $response);

        $this->assertEquals(201, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('hello there', $data['data']['text']);
        $this->assertEquals($userA->id, $data['data']['sender']['id']);
        $this->assertEquals($userB->id, $data['data']['receiver']['id']);
        $this->assertFalse($data['data']['read']);

        $message = Message::whereText('hello there')->first();
        $this->assertEquals($userA->id, $message->user_id);
        $this->assertEquals($userB->id, $message->receiver_id);
    }

    public function testSendToSelf(): void
    {
        $userA = User::factory()->create();

        $this->setExpects($this->events, 'dispatch', ['message.created'], []);

        $controller = $this->controllerWithAuth($userA);
        $request = new Request();
        $request = $request->withAttribute('user_id', (string) $userA->id);
        $request = $request->withParsedBody(['text' => 'note to self']);

        $response = $controller->send($request);
        $data = json_decode($response->getContent(), true);

        $this->assertTrue($data['data']['read']);
    }

    public function testSendNotFound(): void
    {
        $userA = User::factory()->create();
        $controller = $this->controllerWithAuth($userA);

        $request = new Request();
        $request = $request->withAttribute('user_id', '999999');
        $request = $request->withParsedBody(['text' => 'hi']);

        $this->expectException(ModelNotFoundException::class);
        $controller->send($request);
    }

    public function testSendValidationFailure(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $controller = $this->controllerWithAuth($userA);

        $request = new Request();
        $request = $request->withAttribute('user_id', (string) $userB->id);
        $request = $request->withParsedBody([]);

        $this->expectException(ValidationException::class);
        $controller->send($request);
    }

    public function testDelete(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $message = Message::sendMessage($userA, $userB, 'to be deleted');

        $controller = $this->controllerWithAuth($userA);
        $request = new Request();
        $request = $request->withAttribute('msg_id', (string) $message->id);

        $response = $controller->delete($request);

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertNull(Message::find($message->id));
    }

    public function testDeleteForbiddenForOtherUsersMessage(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $message = Message::sendMessage($userA, $userB, 'not yours');

        $controller = $this->controllerWithAuth($userB);
        $request = new Request();
        $request = $request->withAttribute('msg_id', (string) $message->id);

        $this->expectException(HttpForbidden::class);
        $controller->delete($request);
    }

    public function testDeleteNotFound(): void
    {
        $userA = User::factory()->create();
        $controller = $this->controllerWithAuth($userA);

        $request = new Request();
        $request = $request->withAttribute('msg_id', '999999');

        $this->expectException(ModelNotFoundException::class);
        $controller->delete($request);
    }

    public function testSetRead(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $message = Message::sendMessage($userA, $userB, 'please read');

        $controller = $this->controllerWithAuth($userB);
        $request = new Request();
        $request = $request->withAttribute('msg_id', (string) $message->id);
        $request = $request->withParsedBody(['read' => true]);

        $response = $controller->setRead($request);
        $this->validateApiResponse('/messages/{msgId}', 'patch', $response);

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['data']['read']);
        $this->assertTrue(Message::find($message->id)->read);

        // And back again.
        $request = $request->withParsedBody(['read' => false]);
        $controller->setRead($request);
        $this->assertFalse(Message::find($message->id)->read);
    }

    public function testSetReadForbiddenForSender(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $message = Message::sendMessage($userA, $userB, 'please read');

        $controller = $this->controllerWithAuth($userA);
        $request = new Request();
        $request = $request->withAttribute('msg_id', (string) $message->id);
        $request = $request->withParsedBody(['read' => true]);

        $this->expectException(HttpForbidden::class);
        $controller->setRead($request);
    }

    public function testSetReadNotFound(): void
    {
        $userA = User::factory()->create();
        $controller = $this->controllerWithAuth($userA);

        $request = new Request();
        $request = $request->withAttribute('msg_id', '999999');
        $request = $request->withParsedBody(['read' => true]);

        $this->expectException(ModelNotFoundException::class);
        $controller->setRead($request);
    }

    public function testSetReadValidationFailure(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $message = Message::sendMessage($userA, $userB, 'please read');

        $controller = $this->controllerWithAuth($userB);
        $request = new Request();
        $request = $request->withAttribute('msg_id', (string) $message->id);
        $request = $request->withParsedBody([]);

        $this->expectException(ValidationException::class);
        $controller->setRead($request);
    }

    public function testHasPermissionWithUserMessagesPrivilege(): void
    {
        $controller = new MessagesController(new Response());
        $controller->setAuth($this->auth(['user_messages']));

        $this->assertTrue($controller->hasPermission(new Request(), 'index'));
        $this->assertTrue($controller->hasPermission($this->request('POST'), 'send'));
        $this->assertTrue($controller->hasPermission($this->request('PATCH'), 'setRead'));
        $this->assertTrue($controller->hasPermission($this->request('DELETE'), 'delete'));
    }

    public function testHasPermissionApiPrivilegeIsReadOnly(): void
    {
        $controller = new MessagesController(new Response());
        $controller->setAuth($this->auth(['api']));

        $this->assertTrue($controller->hasPermission(new Request(), 'index'));
        $this->assertTrue($controller->hasPermission(new Request(), 'show'));
        $this->assertFalse($controller->hasPermission($this->request('POST'), 'send'));
        $this->assertFalse($controller->hasPermission($this->request('PATCH'), 'setRead'));
        $this->assertFalse($controller->hasPermission($this->request('DELETE'), 'delete'));
    }

    public function testHasPermissionWithoutUserMessagesPrivilege(): void
    {
        $controller = new MessagesController(new Response());
        $controller->setAuth($this->auth(['news']));

        $this->assertFalse($controller->hasPermission(new Request(), 'index'));
        $this->assertFalse($controller->hasPermission($this->request('POST'), 'send'));
    }

    public function testHasPermissionWithoutAuthenticatedUser(): void
    {
        $auth = $this->createMock(Authenticator::class);
        $this->setExpects($auth, 'user', null, null);

        $controller = new MessagesController(new Response());
        $controller->setAuth($auth);

        $this->assertFalse($controller->hasPermission(new Request(), 'index'));
    }

    public function testHasPermissionWithoutAuthSet(): void
    {
        $controller = new MessagesController(new Response());

        $this->assertFalse($controller->hasPermission(new Request(), 'index'));
    }

    /**
     * @param string[] $privileges
     */
    protected function auth(array $privileges): Authenticator
    {
        $auth = $this->createMock(Authenticator::class);
        $auth->method('user')->willReturn(User::factory()->create());
        $auth->method('can')->willReturnCallback(
            fn(array|string $abilities): bool => empty(array_diff((array) $abilities, $privileges))
        );

        return $auth;
    }

    protected function request(string $method): Request
    {
        return (new Request())->withMethod($method);
    }
}
