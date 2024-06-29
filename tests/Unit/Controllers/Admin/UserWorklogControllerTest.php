<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Admin;

use Carbon\Carbon;
use Engelsystem\Controllers\Admin\UserWorklogController;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Exceptions\HttpForbidden;
use Engelsystem\Http\Exceptions\HttpNotFound;
use Engelsystem\Http\Exceptions\ValidationException;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\UrlGenerator;
use Engelsystem\Http\Validation\Validator;
use Engelsystem\Models\User\User;
use Engelsystem\Models\Worklog;
use Engelsystem\Test\Unit\Controllers\ControllerTest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use PHPUnit\Framework\MockObject\MockObject;

class UserWorklogControllerTest extends ControllerTest
{
    protected Authenticator|MockObject $auth;

    protected Redirector|MockObject $redirect;

    protected User $user;

    protected UserWorklogController $controller;

    /**
     * @covers \Engelsystem\Controllers\Admin\UserWorklogController::editWorklog
     * @covers \Engelsystem\Controllers\Admin\UserWorklogController::needsUser
     */
    public function testNeedsUserSelfWorklogDisabled(): void
    {
        $this->config->set('enable_self_worklog', false);
        $request = $this->request->withAttribute('user_id', $this->user->id);
        $this->expectException(HttpForbidden::class);
        $this->controller->editWorklog($request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\UserWorklogController::editWorklog
     * @covers \Engelsystem\Controllers\Admin\UserWorklogController::needsUser
     */
    public function testShowAddWorklogWithUnknownUserIdThrows(): void
    {
        $request = $this->request->withAttribute('user_id', 1234);
        $this->expectException(ModelNotFoundException::class);
        $this->controller->editWorklog($request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\UserWorklogController::editWorklog
     * @covers \Engelsystem\Controllers\Admin\UserWorklogController::__construct
     * @covers \Engelsystem\Controllers\Admin\UserWorklogController::showEditWorklog
     */
    public function testShowAddWorklog(): void
    {
        $request = $this->request->withAttribute('user_id', $this->user->id);
        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $this->assertEquals('admin/user/edit-worklog.twig', $view);
                $this->assertEquals($this->user->id, $data['userdata']->id);
                $this->assertEquals(Carbon::today(), $data['work_date']);
                $this->assertEquals(0, $data['work_hours']);
                $this->assertEquals('', $data['comment']);
                $this->assertFalse($data['is_edit']);
                return $this->response;
            });
        $this->controller->editWorklog($request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\UserWorklogController::editWorklog
     */
    public function testShowEditWorklogWithWorklogNotAssociatedToUserThrows(): void
    {
        /** @var User $user2 */
        $user2 = User::factory()->create();
        /** @var Worklog $worklog */
        $worklog = Worklog::factory(['user_id' => $user2->id])->create();

        $request = $this->request
            ->withAttribute('user_id', $this->user->id)
            ->withAttribute('worklog_id', $worklog->id);
        $this->expectException(HttpNotFound::class);
        $this->controller->editWorklog($request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\UserWorklogController::editWorklog
     */
    public function testShowEditWorklog(): void
    {
        /** @var Worklog $worklog */
        $worklog = Worklog::factory([
            'user_id' => $this->user->id,
            'worked_at' => new Carbon('2022-01-01'),
            'hours' => 3.14,
            'comment' => 'a comment',
        ])->create();

        $request = $this->request
            ->withAttribute('user_id', $this->user->id)
            ->withAttribute('worklog_id', $worklog->id);
        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $this->assertEquals($this->user->id, $data['userdata']->id);
                $this->assertEquals(new Carbon('2022-01-01'), $data['work_date']);
                $this->assertEquals(3.14, $data['work_hours']);
                $this->assertEquals('a comment', $data['comment']);
                $this->assertTrue($data['is_edit']);
                return $this->response;
            });
        $this->controller->editWorklog($request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\UserWorklogController::saveWorklog
     * @covers \Engelsystem\Controllers\Admin\UserWorklogController::needsUser
     */
    public function testSaveWorklogWithUnknownUserIdThrows(): void
    {
        $request = $this->request->withAttribute('user_id', 1234)->withParsedBody([]);
        $this->expectException(ModelNotFoundException::class);
        $this->controller->saveWorklog($request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\UserWorklogController::saveWorklog
     *
     * @dataProvider invalidSaveWorklogParams
     */
    public function testSaveWorklogWithInvalidParamsThrows(array $body): void
    {
        $request = $this->request->withAttribute('user_id', $this->user->id)->withParsedBody($body);
        $this->expectException(ValidationException::class);
        $this->controller->saveWorklog($request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\UserWorklogController::saveWorklog
     */
    public function testSaveNewWorklog(): void
    {
        $work_date = Carbon::today();
        $work_hours = 3.14;
        $comment = str_repeat('X', 200);
        $body = ['work_date' => $work_date, 'work_hours' => $work_hours, 'comment' => $comment];
        $request = $this->request->withAttribute('user_id', $this->user->id)->withParsedBody($body);
        $this->setExpects($this->auth, 'user', null, $this->user, $this->any());
        $this->redirect->expects($this->once())
            ->method('to')
            ->with('/users?action=view&user_id=' . $this->user->id)
            ->willReturn($this->response);

        $this->controller->saveWorklog($request);

        $this->assertHasNotification('worklog.add.success');
        $this->assertTrue($this->log->hasInfoThatContains('Added worklog for'));

        $this->assertEquals(1, $this->user->worklogs->count());
        $new_worklog = $this->user->worklogs[0];
        $this->assertEquals($this->user->id, $new_worklog->user->id);
        $this->assertEquals($work_date, $new_worklog->worked_at);
        $this->assertEquals($work_hours, $new_worklog->hours);
        $this->assertEquals($comment, $new_worklog->comment);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\UserWorklogController::saveWorklog
     * @covers \Engelsystem\Controllers\Admin\UserWorklogController::needsUser
     */
    public function testOverwriteWorklogWithUnknownWorklogIdThrows(): void
    {
        $body = ['work_date' => Carbon::today(), 'work_hours' => 3.14, 'comment' => 'a comment'];
        $request = $this->request
            ->withAttribute('user_id', $this->user->id)
            ->withAttribute('worklog_id', 1234)
            ->withParsedBody($body);
        $this->expectException(ModelNotFoundException::class);
        $this->controller->saveWorklog($request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\UserWorklogController::saveWorklog
     */
    public function testOverwriteWorklogWithWorklogNotAssociatedToUserThrows(): void
    {
        /** @var User $user2 */
        $user2 = User::factory()->create();
        /** @var Worklog $worklog */
        $worklog = Worklog::factory(['user_id' => $user2->id])->create();

        $body = ['work_date' => Carbon::today(), 'work_hours' => 3.14, 'comment' => 'a comment'];
        $request = $this->request
            ->withAttribute('user_id', $this->user->id)
            ->withAttribute('worklog_id', $worklog->id)
            ->withParsedBody($body);
        $this->expectException(HttpNotFound::class);
        $this->controller->saveWorklog($request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\UserWorklogController::saveWorklog
     */
    public function testOverwriteWorklog(): void
    {
        /** @var Worklog $worklog */
        $worklog = Worklog::factory(['user_id' => $this->user->id])->create();
        $work_date = Carbon::today();
        $work_hours = 3.14;
        $comment = str_repeat('X', 200);
        $body = ['work_date' => $work_date, 'work_hours' => $work_hours, 'comment' => $comment];

        $request = $this->request
            ->withAttribute('user_id', $this->user->id)
            ->withAttribute('worklog_id', $worklog->id)
            ->withParsedBody($body);
        $this->setExpects($this->auth, 'user', null, $this->user, $this->any());
        $this->redirect->expects($this->once())
            ->method('to')
            ->with('/users?action=view&user_id=' . $this->user->id)
            ->willReturn($this->response);

        $this->controller->saveWorklog($request);

        $this->assertHasNotification('worklog.edit.success');
        $worklog = Worklog::find($worklog->id);
        $this->assertEquals($work_date, $worklog->worked_at);
        $this->assertEquals($work_hours, $worklog->hours);
        $this->assertEquals($comment, $worklog->comment);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\UserWorklogController::showDeleteWorklog
     */
    public function testShowDeleteWorklogWithWorklogNotAssociatedToUserThrows(): void
    {
        /** @var User $user2 */
        $user2 = User::factory()->create();
        /** @var Worklog $worklog */
        $worklog = Worklog::factory(['user_id' => $user2->id])->create();

        $request = $this->request
            ->withAttribute('user_id', $this->user->id)
            ->withAttribute('worklog_id', $worklog->id);
        $this->expectException(HttpNotFound::class);
        $this->controller->showDeleteWorklog($request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\UserWorklogController::showDeleteWorklog
     */
    public function testShowDeleteWorklog(): void
    {
        /** @var Worklog $worklog */
        $worklog = Worklog::factory(['user_id' => $this->user->id])->create();

        $request = $this->request
            ->withAttribute('user_id', $this->user->id)
            ->withAttribute('worklog_id', $worklog->id);
        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $this->assertEquals($this->user->id, $data['userdata']->id);
                return $this->response;
            });
        $this->controller->showDeleteWorklog($request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\UserWorklogController::deleteWorklog
     * @covers \Engelsystem\Controllers\Admin\UserWorklogController::needsUser
     */
    public function testDeleteWorklogWithUnknownWorklogIdThrows(): void
    {
        $request = $this->request
            ->withAttribute('user_id', $this->user->id)
            ->withAttribute('worklog_id', 1234);
        $this->expectException(ModelNotFoundException::class);
        $this->controller->deleteWorklog($request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\UserWorklogController::deleteWorklog
     */
    public function testDeleteWorklogWithWorklogNotAssociatedToUserThrows(): void
    {
        /** @var User $user2 */
        $user2 = User::factory()->create();
        /** @var Worklog $worklog */
        $worklog = Worklog::factory(['user_id' => $user2->id])->create();

        $request = $this->request
            ->withAttribute('user_id', $this->user->id)
            ->withAttribute('worklog_id', $worklog->id);
        $this->expectException(HttpNotFound::class);
        $this->controller->deleteWorklog($request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\UserWorklogController::deleteWorklog
     */
    public function testDeleteWorklog(): void
    {
        /** @var Worklog $worklog */
        $worklog = Worklog::factory(['user_id' => $this->user->id])->create();

        $request = $this->request
            ->withAttribute('user_id', $this->user->id)
            ->withAttribute('worklog_id', $worklog->id);
        $this->setExpects($this->auth, 'user', null, $this->user, $this->any());
        $this->redirect->expects($this->once())
            ->method('to')
            ->with('/users?action=view&user_id=' . $this->user->id)
            ->willReturn($this->response);

        $this->controller->deleteWorklog($request);

        $this->log->hasInfoThatContains('Deleted worklog');
        $this->assertHasNotification('worklog.delete.success');
        $worklog = Worklog::find($worklog->id);
        $this->assertNull($worklog);
    }

    /**
     * @return array[]
     */
    public function invalidSaveWorklogParams(): array
    {
        $today = Carbon::today();
        return [
            // missing work_date
            [['work_hours' => 3.14, 'comment' => 'com']],
            // missing work_hours
            [['work_date' => $today, 'comment' => 'com']],
            // missing comment
            [['work_date' => $today, 'work_hours' => 3.14]],
            // too low work_hours
            [['work_date' => $today, 'work_hours' => -.1, 'comment' => 'com']],
            // too low work_hours
            [['work_date' => $today, 'work_hours' => 3.14, 'comment' => str_repeat('X', 201)]],
        ];
    }

    /**
     * @return array[]
     */
    public function buildupConfigsAndWorkDates(): array
    {
        $day_before_yesterday = Carbon::today()->subDays(2);
        $yesterday = Carbon::yesterday();
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();

        // buildup_start, event_start, suggested work date
        return [
            [null, null, $today],
            [$yesterday, null, $yesterday],
            [$yesterday, $tomorrow, $today],
            [$day_before_yesterday, $yesterday, $day_before_yesterday],
        ];
    }

    /**
     * Setup environment
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->config->set('enable_self_worklog', true);

        $this->app->bind('http.urlGenerator', UrlGenerator::class);

        $this->auth = $this->createMock(Authenticator::class);
        $this->app->instance(Authenticator::class, $this->auth);

        $this->redirect = $this->createMock(Redirector::class);
        $this->app->instance(Redirector::class, $this->redirect);

        $this->user = User::factory()->create();
        $this->setExpects($this->auth, 'user', null, $this->user, $this->any());

        $this->controller = $this->app->make(UserWorklogController::class);
        $this->controller->setValidator(new Validator());
    }
}
