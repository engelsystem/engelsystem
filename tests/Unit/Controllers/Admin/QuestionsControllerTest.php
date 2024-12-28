<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Admin;

use Carbon\Carbon;
use Engelsystem\Controllers\Admin\QuestionsController;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Exceptions\ValidationException;
use Engelsystem\Http\UrlGenerator;
use Engelsystem\Http\UrlGeneratorInterface;
use Engelsystem\Http\Validation\Validator;
use Engelsystem\Models\Question;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\Controllers\ControllerTest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use PHPUnit\Framework\MockObject\MockObject;

class QuestionsControllerTest extends ControllerTest
{
    protected Authenticator|MockObject $auth;

    protected User $user;

    /**
     * @covers \Engelsystem\Controllers\Admin\QuestionsController::index
     * @covers \Engelsystem\Controllers\Admin\QuestionsController::__construct
     */
    public function testIndex(): void
    {
        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $this->assertEquals('pages/questions/index.twig', $view);
                $this->assertArrayHasKey('questions', $data);
                $this->assertArrayHasKey('is_admin', $data);

                $this->assertEquals('Foobar?', $data['questions'][0]->text);
                $this->assertTrue($data['is_admin']);

                return $this->response;
            });

        /** @var QuestionsController $controller */
        $controller = $this->app->get(QuestionsController::class);
        $controller->index();
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\QuestionsController::delete
     */
    public function testDeleteInvalidRequest(): void
    {
        /** @var QuestionsController $controller */
        $controller = $this->app->get(QuestionsController::class);
        $controller->setValidator($this->app->get(Validator::class));

        $this->expectException(ValidationException::class);
        $controller->delete($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\QuestionsController::delete
     */
    public function testDeleteNotFound(): void
    {
        $this->request = $this->request->withParsedBody(['id' => 42, 'delete' => '1']);

        /** @var QuestionsController $controller */
        $controller = $this->app->get(QuestionsController::class);
        $controller->setValidator($this->app->get(Validator::class));

        $this->expectException(ModelNotFoundException::class);
        $controller->delete($this->request);
    }

        /**
     * @covers \Engelsystem\Controllers\Admin\QuestionsController::delete
     */
    public function testDelete(): void
    {
        $this->request = $this->request->withParsedBody(['id' => 1, 'delete' => '1']);
        $this->setExpects($this->response, 'redirectTo', ['http://localhost/admin/questions'], $this->response);

        /** @var QuestionsController $controller */
        $controller = $this->app->get(QuestionsController::class);
        $controller->setValidator($this->app->get(Validator::class));

        $controller->delete($this->request);

        $this->assertCount(1, Question::all());
        $this->assertTrue($this->log->hasInfoThatContains('Deleted question'));
        $this->assertHasNotification('question.delete.success');
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\QuestionsController::edit
     * @covers \Engelsystem\Controllers\Admin\QuestionsController::showEdit
     */
    public function testEdit(): void
    {
        $this->request->attributes->set('question_id', 1);
        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $this->assertEquals('pages/questions/edit.twig', $view);
                $this->assertArrayHasKey('question', $data);
                $this->assertArrayHasKey('is_admin', $data);

                $this->assertEquals('Question?', $data['question']->text);
                $this->assertEquals($this->user->id, $data['question']->user->id);
                $this->assertTrue($data['is_admin']);

                return $this->response;
            });

        /** @var QuestionsController $controller */
        $controller = $this->app->get(QuestionsController::class);

        $controller->edit($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\QuestionsController::edit
     */
    public function testEditNotFound(): void
    {
        $this->request->attributes->set('question_id', 42);
        $this->expectException(ModelNotFoundException::class);

        /** @var QuestionsController $controller */
        $controller = $this->app->get(QuestionsController::class);

        $controller->edit($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\QuestionsController::save
     */
    public function testSaveNotFound(): void
    {
        $this->expectException(ModelNotFoundException::class);

        /** @var QuestionsController $controller */
        $controller = $this->app->make(QuestionsController::class);
        $controller->setValidator(new Validator());
        $controller->save($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\QuestionsController::save
     */
    public function testSaveCreateInvalid(): void
    {
        $this->request->attributes->set('question_id', 1);
        $this->expectException(ValidationException::class);

        /** @var QuestionsController $controller */
        $controller = $this->app->make(QuestionsController::class);
        $controller->setValidator(new Validator());
        $controller->save($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\QuestionsController::save
     */
    public function testSaveCreateEdit(): void
    {
        $this->request->attributes->set('question_id', 2);
        $body = [
            'text' => 'Foo?',
            'answer' => 'Bar!',
        ];

        $this->request = $this->request->withParsedBody($body);
        $this->response->expects($this->once())
            ->method('redirectTo')
            ->with('http://localhost/admin/questions')
            ->willReturn($this->response);

        /** @var QuestionsController $controller */
        $controller = $this->app->make(QuestionsController::class);
        $controller->setValidator(new Validator());

        $controller->save($this->request);

        $this->assertTrue($this->log->hasInfoThatContains('Saved'));
        $this->assertHasNotification('question.edit.success');

        $question = Question::find(2);
        $this->assertEquals('Foo?', $question->text);
        $this->assertEquals('Bar!', $question->answer);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\QuestionsController::save
     */
    public function testSavePreview(): void
    {
        $this->request->attributes->set('question_id', 1);
        $this->request = $this->request->withParsedBody([
            'text'    => 'Foo?',
            'answer'  => 'Bar!',
            'preview' => '1',
        ]);
        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function ($view, $data) {
                $this->assertEquals('pages/questions/edit.twig', $view);

                /** @var Question $question */
                $question = $data['question'];
                // Contains new text
                $this->assertEquals('Foo?', $question->text);
                $this->assertEquals('Bar!', $question->answer);

                return $this->response;
            });

        /** @var QuestionsController $controller */
        $controller = $this->app->make(QuestionsController::class);
        $controller->setValidator(new Validator());

        $controller->save($this->request);

        // Assert no changes
        $question = Question::find(1);
        $this->assertEquals('Question?', $question->text);
        $this->assertEquals('Answer!', $question->answer);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\QuestionsController::save
     */
    public function testSaveDelete(): void
    {
        $this->request->attributes->set('question_id', 1);
        $this->request = $this->request->withParsedBody([
            'text'   => '.',
            'answer' => '.',
            'delete' => '1',
        ]);
        $this->response->expects($this->once())
            ->method('redirectTo')
            ->with('http://localhost/admin/questions')
            ->willReturn($this->response);

        /** @var QuestionsController $controller */
        $controller = $this->app->make(QuestionsController::class);
        $controller->setValidator(new Validator());

        $controller->save($this->request);

        $this->assertCount(1, Question::all());
        $this->assertTrue($this->log->hasInfoThatContains('Deleted question'));
        $this->assertHasNotification('question.delete.success');
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

        $this->user = User::factory()->create();
        $this->setExpects($this->auth, 'user', null, $this->user, $this->any());

        (new Question([
            'user_id'     => $this->user->id,
            'text'        => 'Question?',
            'answerer_id' => $this->user->id,
            'answer'      => 'Answer!',
            'answered_at' => new Carbon(),
        ]))->save();

        (new Question([
            'user_id'     => $this->user->id,
            'text'        => 'Foobar?',
        ]))->save();
    }
}
