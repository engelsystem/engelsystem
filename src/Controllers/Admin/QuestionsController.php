<?php

namespace Engelsystem\Controllers\Admin;

use Carbon\Carbon;
use Engelsystem\Controllers\BaseController;
use Engelsystem\Controllers\HasUserNotifications;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\Question;
use Psr\Log\LoggerInterface;

class QuestionsController extends BaseController
{
    use HasUserNotifications;

    protected Authenticator $auth;

    protected LoggerInterface $log;

    protected Question $question;

    protected Redirector $redirect;

    protected Response $response;

    /** @var array */
    protected array $permissions = [
        'question.add',
        'question.edit',
    ];

    public function __construct(
        Authenticator $auth,
        LoggerInterface $log,
        Question $question,
        Redirector $redirector,
        Response $response
    ) {
        $this->auth = $auth;
        $this->log = $log;
        $this->question = $question;
        $this->redirect = $redirector;
        $this->response = $response;
    }

    public function index(): Response
    {
        $questions = $this->question
            ->orderBy('answered_at')
            ->orderByDesc('created_at')
            ->get();

        return $this->response->withView(
            'pages/questions/overview.twig',
            ['questions' => $questions, 'is_admin' => true] + $this->getNotifications()
        );
    }

    public function delete(Request $request): Response
    {
        $data = $this->validate($request, [
            'id'     => 'required|int',
            'delete' => 'checked',
        ]);

        $question = $this->question->findOrFail($data['id']);
        $question->delete();

        $this->log->info('Deleted question {question}', ['question' => $question->text]);
        $this->addNotification('question.delete.success');

        return $this->redirect->to('/admin/questions');
    }

    public function edit(Request $request): Response
    {
        $questionId = (int)$request->getAttribute('question_id');

        $questions = $this->question->find($questionId);

        return $this->showEdit($questions);
    }

    public function save(Request $request): Response
    {
        $questionId = (int)$request->getAttribute('question_id');

        /** @var Question $question */
        $question = $this->question->findOrNew($questionId);

        $data = $this->validate($request, [
            'text'    => 'required',
            'answer'  => 'required',
            'delete'  => 'optional|checked',
            'preview' => 'optional|checked',
        ]);

        if (!is_null($data['delete'])) {
            $question->delete();

            $this->log->info('Deleted question "{question}"', ['question' => $question->text]);

            $this->addNotification('question.delete.success');

            return $this->redirect->to('/admin/questions');
        }

        $question->text = $data['text'];
        $question->answer = $data['answer'];
        $question->answered_at = Carbon::now();
        $question->answerer()->associate($this->auth->user());

        if (!is_null($data['preview'])) {
            return $this->showEdit($question);
        }

        $question->save();

        $this->log->info(
            'Updated questions "{text}": {answer}',
            ['text' => $question->text, 'answer' => $question->answer]
        );

        $this->addNotification('question.edit.success');

        return $this->redirect->to('/admin/questions');
    }

    protected function showEdit(?Question $question): Response
    {
        return $this->response->withView(
            'pages/questions/edit.twig',
            ['question' => $question, 'is_admin' => true] + $this->getNotifications()
        );
    }
}
