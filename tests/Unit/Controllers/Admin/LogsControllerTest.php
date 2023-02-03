<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Admin;

use Engelsystem\Controllers\Admin\LogsController;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\LogEntry;
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
        $alert = $log->find($alert)->first();
        $error = $log->create(['level' => LogLevel::ERROR, 'message' => 'Error test']);
        $error = $log->find($error)->first();

        $response = $this->createMock(Response::class);
        $response->expects($this->exactly(2))
            ->method('withView')
            ->withConsecutive(
                ['admin/log.twig', ['entries' => new Collection([$error, $alert]), 'search' => null]],
                ['admin/log.twig', ['entries' => new Collection([$error]), 'search' => 'error']]
            )
            ->willReturn($response);

        $request = Request::create('/');

        $controller = new LogsController($log, $response);
        $controller->index($request);

        $request->request->set('search', 'error');
        $controller->index($request);
    }

    /**
     * Setup the DB
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->initDatabase();
    }
}
