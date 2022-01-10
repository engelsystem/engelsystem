<?php

namespace Engelsystem\Test\Unit\Controllers;

use Carbon\Carbon;
use Engelsystem\Controllers\QuestionsController;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Exceptions\HttpForbidden;
use Engelsystem\Http\Exceptions\ValidationException;
use Engelsystem\Http\UrlGenerator;
use Engelsystem\Http\UrlGeneratorInterface;
use Engelsystem\Http\Validation\Validator;
use Engelsystem\Models\Question;
use Engelsystem\Models\User\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use PHPUnit\Framework\MockObject\MockObject;

class QuestionsControllerTest extends ControllerTest
{
    /** @var Authenticator|MockObject */
    protected $auth;

    /** @var User */
    protected $user;

    /**
     * @covers \Engelsystem\Controllers\QuestionsController::index
     * @covers \Engelsystem\Controllers\QuestionsController::__construct
     */
    public function testIndex()
    {
        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $this->assertEquals('pages/questions/overview.twig', $view);
                $this->assertArrayHasKey('questions', $data);

                $this->assertEquals('Foo?', $data['questions'][0]->text);

                return $this->response;
            });

        /** @var QuestionsController $controller */
        $controller = $this->app->get(QuestionsController::class);
        $controller->index();
    }

    /**
     * @covers \Engelsystem\Controllers\QuestionsController::add
     */
    public function testAdd()
    {
        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $this->assertEquals('pages/questions/edit.twig', $view);
                $this->assertArrayHasKey('question', $data);
                $this->assertNull($data['question']);

                return $this->response;
            });

        /** @var QuestionsController $controller */
        $controller = $this->app->get(QuestionsController::class);
        $controller->setValidator(new Validator());

        $controller->add();
    }

    /**
     * @covers \Engelsystem\Controllers\QuestionsController::delete
     */
    public function testDeleteNotFound()
    {
        $this->request = $this->request->withParsedBody([
            'id'     => '3',
            'delete' => '1',
        ]);

        /** @var QuestionsController $controller */
        $controller = $this->app->get(QuestionsController::class);
        $controller->setValidator(new Validator());

        $this->expectException(ModelNotFoundException::class);
        $controller->delete($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\QuestionsController::delete
     */
    public function testDeleteNotOwn()
    {
        $otherUser = User::factory()->create();
        (new Question([
            'user_id' => $otherUser->id,
            'text'    => 'Lorem?',
        ]))->save();
        $this->request = $this->request->withParsedBody([
            'id'     => '3',
            'delete' => '1',
        ]);

        /** @var QuestionsController $controller */
        $controller = $this->app->get(QuestionsController::class);
        $controller->setValidator(new Validator());

        $this->expectException(HttpForbidden::class);
        $controller->delete($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\QuestionsController::delete
     */
    public function testDelete()
    {
        $this->request = $this->request->withParsedBody([
            'id'     => '2',
            'delete' => '1',
        ]);
        $this->response->expects($this->once())
            ->method('redirectTo')
            ->with('http://localhost/questions')
            ->willReturn($this->response);

        /** @var QuestionsController $controller */
        $controller = $this->app->get(QuestionsController::class);
        $controller->setValidator(new Validator());

        $controller->delete($this->request);

        $this->assertCount(1, Question::all());
        $this->assertTrue($this->log->hasInfoThatContains('Deleted own question'));
        $this->assertHasNotification('question.delete.success');
    }

    /**
     * @covers \Engelsystem\Controllers\QuestionsController::save
     */
    public function testSaveInvalid()
    {
        /** @var QuestionsController $controller */
        $controller = $this->app->get(QuestionsController::class);
        $controller->setValidator(new Validator());

        $this->expectException(ValidationException::class);
        $controller->save($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\QuestionsController::save
     */
    public function testSave()
    {
        $this->request = $this->request->withParsedBody([
            'text' => 'Some question?',
        ]);
        $this->response->expects($this->once())
            ->method('redirectTo')
            ->with('http://localhost/questions')
            ->willReturn($this->response);

        /** @var QuestionsController $controller */
        $controller = $this->app->get(QuestionsController::class);
        $controller->setValidator(new Validator());

        $controller->save($this->request);

        $this->assertCount(3, Question::all());
        $this->assertTrue($this->log->hasInfoThatContains('Asked'));
        $this->assertHasNotification('question.add.success');
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
            'text'        => 'Lorem?',
        ]))->save();

        (new Question([
            'user_id'     => $this->user->id,
            'text'        => 'Foo?',
            'answerer_id' => $this->user->id,
            'answer'      => 'Bar!',
            'answered_at' => new Carbon(),
        ]))->save();
    }
}
