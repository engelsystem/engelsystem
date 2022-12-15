<?php

namespace Engelsystem\Controllers;

use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Exceptions\HttpForbidden;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\Question;
use Psr\Log\LoggerInterface;

class QuestionsController extends BaseController
{
    use HasUserNotifications;

    /** @var string[] */
    protected array $permissions = [
        'question.add',
    ];

    public function __construct(
        protected Authenticator $auth,
        protected LoggerInterface $log,
        protected Question $question,
        protected Redirector $redirect,
        protected Response $response
    ) {
    }

    public function index(): Response
    {
        $questions = $this->question
            ->whereUserId($this->auth->user()->id)
            ->orderByDesc('answered_at')
            ->orderBy('created_at')
            ->get();

        return $this->response->withView(
            'pages/questions/overview.twig',
            ['questions' => $questions] + $this->getNotifications()
        );
    }

    public function add(): Response
    {
        return $this->response->withView(
            'pages/questions/edit.twig',
            ['question' => null] + $this->getNotifications()
        );
    }

    public function delete(Request $request): Response
    {
        $data = $this->validate(
            $request,
            [
                'id'     => 'int|required',
                'delete' => 'checked',
            ]
        );

        $question = $this->question->findOrFail($data['id']);
        if ($question->user->id != $this->auth->user()->id) {
            throw new HttpForbidden();
        }

        $question->delete();

        $this->log->info('Deleted own question {question}', ['question' => $question->text]);
        $this->addNotification('question.delete.success');

        return $this->redirect->to('/questions');
    }

    public function save(Request $request): Response
    {
        $data = $this->validate(
            $request,
            [
                'text' => 'required',
            ]
        );

        $question = new Question();
        $question->user()->associate($this->auth->user());
        $question->text = $data['text'];
        $question->save();

        $this->log->info(
            'Asked: {question}',
            [
                'question' => $question->text,
            ]
        );

        $this->addNotification('question.add.success');

        return $this->redirect->to('/questions');
    }
}
