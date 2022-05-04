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
use Engelsystem\Models\User\Settings;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\HasDatabase;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use PHPUnit\Framework\MockObject\MockObject;

class MessagesControllerTest extends ControllerTest
{
    use HasDatabase;

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

    public function testIndex()
    {
        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $this->assertEquals('pages/messages/overview.twig', $view);
                $this->assertArrayHasKey('conversations', $data);
                $this->assertArrayHasKey('users', $data);

                $conversations = $data['conversations'];
                $users = $data['users'];

                $this->assertEquals(2, count($conversations));
                $this->assertEquals('b', $conversations[0]['other_user']->name);
                $this->assertEquals('c', $conversations[1]['other_user']->name);

                return $this->response;
            });

        /** @var MessagesController $controller */
        $controller = $this->app->get(MessagesController::class);
        $controller->index();
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

        $just_now = Carbon::now();
        $one_minute_ago = Carbon::now()->subMinute();
        $two_minutes_ago = Carbon::now()->subMinutes(2);

        Message::unguard(); // unguard temporarily to assign custom created_at/updated_at values.
        $this->create_message($this->user_a, $this->user_b, 'a>b 1', $two_minutes_ago);
        Message::reguard();
    }

    protected function create_message(User $from, User $to, string $text, Carbon $at)
    {
        (new Message([
            'user_id' => $from->id,
            'receiver_id' => $to->id,
            'text' => $text,
            'created_at' => $at,
            'updated_at' => $at,
        ]))->save();
    }

}
