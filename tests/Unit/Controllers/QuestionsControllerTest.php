<?php

declare(strict_types=1);

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
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversMethod(QuestionsController::class, 'index')]
#[CoversMethod(QuestionsController::class, '__construct')]
#[CoversMethod(QuestionsController::class, 'add')]
#[CoversMethod(QuestionsController::class, 'delete')]
#[CoversMethod(QuestionsController::class, 'save')]
#[AllowMockObjectsWithoutExpectations]
class QuestionsControllerTest extends ControllerTestCase
{
    protected Authenticator&MockObject $auth;

    protected User $user;

    public function testIndex(): void
    {
        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $this->assertEquals('pages/questions/index.twig', $view);
                $this->assertArrayHasKey('questions', $data);

                $this->assertEquals('Foo?', $data['questions'][0]->text);

                return $this->response;
            });

        /** @var QuestionsController $controller */
        $controller = $this->app->get(QuestionsController::class);
        $controller->index();
    }

    public function testAdd(): void
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

    public function testDeleteNotFound(): void
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

    public function testDeleteNotOwn(): void
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

    public function testDelete(): void
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

    public function testSaveInvalid(): void
    {
        /** @var QuestionsController $controller */
        $controller = $this->app->get(QuestionsController::class);
        $controller->setValidator(new Validator());

        $this->expectException(ValidationException::class);
        $controller->save($this->request);
    }

    public function testSave(): void
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
