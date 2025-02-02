<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Admin;

use Engelsystem\Config\Config;
use Engelsystem\Controllers\Admin\LogsController;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\LogEntry;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\TestCase;
use Illuminate\Database\Eloquent\Collection;
use Psr\Log\LogLevel;

class LogsControllerTest extends TestCase
{
    use HasDatabase;

    /**
     * @covers \Engelsystem\Controllers\Admin\LogsController::index
     * @covers \Engelsystem\Controllers\Admin\LogsController::__construct
     */
    public function testIndex(): void
    {
        $log = new LogEntry();
        $alert = $log->create(['level' => LogLevel::ALERT, 'message' => 'Alert test']);
        $alert = $log->with('user')->find($alert)->first();
        $error = $log->create(['level' => LogLevel::ERROR, 'message' => 'Error test']);
        $error = $log->with('user')->find($error)->first();

        $auth = $this->createMock(Authenticator::class);
        $this->setExpects($auth, 'user', null, null, 2);
        $this->setExpects($auth, 'can', ['logs.all'], true, 2);

        $response = $this->createMock(Response::class);
        $levels = [
            LogLevel::ALERT => 'Alert',
            LogLevel::CRITICAL => 'Critical',
            LogLevel::DEBUG => 'Debug',
            LogLevel::EMERGENCY => 'Emergency',
            LogLevel::ERROR => 'Error',
            LogLevel::INFO => 'Info',
            LogLevel::NOTICE  => 'Notice',
            LogLevel::WARNING => 'Warning',
        ];
        $response->expects($this->exactly(2))
            ->method('withView')
            ->withConsecutive(
                ['admin/log.twig', [
                    'entries' => new Collection([$error, $alert]),
                    'search' => null,
                    'users' => new Collection(),
                    'search_user_id' => null,
                    'level' => null,
                    'levels' => $levels,
                ]],
                ['admin/log.twig', [
                    'entries' => new Collection([$error]),
                    'search' => 'error',
                    'users' => new Collection(),
                    'search_user_id' => null,
                    'level' => null,
                    'levels' => $levels,
                ]]
            )
            ->willReturn($response);

        $request = Request::create('/');

        $controller = new LogsController($log, $response, $auth);
        $controller->index($request);

        $request->request->set('search', 'error');
        $controller->index($request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\LogsController::index
     */
    public function testIndexUser(): void
    {
        User::factory()->create();
        $user = User::with(['personalData', 'state'])->first();

        $log = new LogEntry();
        $alert = $log->create(['level' => LogLevel::ALERT, 'message' => 'Users message', 'user_id' => $user->id]);
        /** @var LogEntry $alert */
        $alert = $log->with('user')->find($alert)->first();
        $log->create(['level' => LogLevel::ERROR, 'message' => 'Error test']);

        $auth = $this->createMock(Authenticator::class);
        $this->setExpects($auth, 'user', null, $user);
        $this->setExpects($auth, 'can', ['logs.all'], false);

        $response = $this->createMock(Response::class);
        $response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) use ($alert, $response) {
                $this->assertEquals('admin/log.twig', $view);
                $this->assertArrayHasKey('entries', $data);
                $this->assertCount(1, $data['entries']);
                $this->assertEquals($alert->message, $data['entries'][0]['message']);
                return $response;
            });

        $request = Request::create('/');

        $controller = new LogsController($log, $response, $auth);
        $controller->index($request);
    }

    /**
     * Set up the DB
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->initDatabase();
        $this->app->instance('config', new Config([]));
    }
}
