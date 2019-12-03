<?php

use Engelsystem\Models\Question;

/**
 * @return string
 */
function admin_questions_title()
{
    return __('Answer questions');
}

/**
 * Renders a hint for new questions to answer.
 *
 * @return string|null
 */
function admin_new_questions()
{
    if (current_page() != 'admin_questions') {
        if (auth()->can('admin_questions')) {
            $unanswered_questions = Question::unanswered()->count();
            if ($unanswered_questions > 0) {
                return '<a href="' . page_link_to('admin_questions') . '">'
                    . __('There are unanswered questions!')
                    . '</a>';
            }
        }
    }

    return null;
}

/**
 * @return string
 */
function admin_questions()
{
    $user = auth()->user();
    $request = request();

    if (!$request->has('action')) {
        $unanswered_questions_table = [];
        $unanswered_questions = Question::unanswered()->get();

        foreach ($unanswered_questions as $question) {
            /* @var Question $question */
            $user_source = $question->user;

            $unanswered_questions_table[] = [
                'from'     => User_Nick_render($user_source),
                'question' => nl2br(htmlspecialchars($question->text)),
                'answer'   => form([
                    form_textarea('answer', '', ''),
                    form_submit('submit', __('Save'))
                ], page_link_to('admin_questions', ['action' => 'answer', 'id' => $question->id])),
                'actions'  => form([
                    form_submit('submit', __('delete'), 'btn-xs'),
                ], page_link_to('admin_questions', ['action' => 'delete', 'id' => $question->id])),
            ];
        }

        $answered_questions_table = [];
        $answered_questions = Question::answered()->get();

        foreach ($answered_questions as $question) {
            /* @var Question $question */
            $user_source = $question->user;
            $answer_user_source = $question->answerer;
            $answered_questions_table[] = [
                'from'        => User_Nick_render($user_source),
                'question'    => nl2br(htmlspecialchars($question->text)),
                'answered_by' => User_Nick_render($answer_user_source),
                'answer'      => nl2br(htmlspecialchars($question->answer)),
                'actions'     => form([
                    form_submit('submit', __('delete'), 'btn-xs')
                ], page_link_to('admin_questions', ['action' => 'delete', 'id' => $question->id]))
            ];
        }

        return page_with_title(admin_questions_title(), [
            '<h2>' . __('Unanswered questions') . '</h2>',
            table([
                'from'     => __('From'),
                'question' => __('Question'),
                'answer'   => __('Answer'),
                'actions'  => ''
            ], $unanswered_questions_table),
            '<h2>' . __('Answered questions') . '</h2>',
            table([
                'from'        => __('From'),
                'question'    => __('Question'),
                'answered_by' => __('Answered by'),
                'answer'      => __('Answer'),
                'actions'     => ''
            ], $answered_questions_table)
        ]);
    } else {
        switch ($request->input('action')) {
            case 'answer':
                if (
                    $request->has('id')
                    && preg_match('/^\d{1,11}$/', $request->input('id'))
                    && $request->hasPostData('submit')
                ) {
                    $question_id = $request->input('id');
                } else {
                    return error('Incomplete call, missing Question ID.', true);
                }

                $question = Question::find($question_id);
                if (!empty($question) && empty($question->answerer_id)) {
                    $answer = trim($request->input('answer'));

                    if (!empty($answer)) {
                        $question->answerer_id = $user->id;
                        $question->answer = $answer;
                        $question->save();
                        engelsystem_log(
                            'Question '
                            . $question['Question']
                            . ' answered: '
                            . $answer
                        );
                        redirect(page_link_to('admin_questions'));
                    } else {
                        return error('Enter an answer!', true);
                    }
                } else {
                    return error('No question found.', true);
                }
                break;
            case 'delete':
                if (
                    $request->has('id')
                    && preg_match('/^\d{1,11}$/', $request->input('id'))
                    && $request->hasPostData('submit')
                ) {
                    $question_id = $request->input('id');
                } else {
                    return error('Incomplete call, missing Question ID.', true);
                }

                $question = Question::find($question_id);
                if (!empty($question)) {
                    $question->delete();
                    engelsystem_log('Question deleted: ' . $question['Question']);
                    redirect(page_link_to('admin_questions'));
                } else {
                    return error('No question found.', true);
                }
                break;
        }
    }

    return '';
}
