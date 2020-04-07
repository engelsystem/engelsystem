<?php

use Engelsystem\Models\Question;

/**
 * @param Question[] $open_questions
 * @param Question[] $answered_questions
 * @param string  $ask_action
 * @return string
 */
function Questions_view(array $open_questions, array $answered_questions, $ask_action)
{
    $open_questions = array_map(
        static function (Question $question): array {
            return [
                'actions'  => form(
                    [
                        form_submit('submit', __('delete'), 'btn-default btn-xs')
                    ],
                    page_link_to('user_questions', ['action' => 'delete', 'id' => $question->id])
                ),
                'Question' => nl2br(htmlspecialchars($question->text)),
            ];
        },
        $open_questions
    );

    $answered_questions = array_map(
        static function (Question $question): array {
            return [
                'Question' => nl2br(htmlspecialchars($question->text)),
                'Answer'   => nl2br(htmlspecialchars($question->answer)),
                'answer_user' => User_Nick_render($question->answerer),
                'actions'  => form(
                    [
                        form_submit('submit', __('delete'), 'btn-default btn-xs')
                    ],
                    page_link_to('user_questions', ['action' => 'delete', 'id' => $question->id])
                ),
            ];
        },
        $answered_questions
    );

    return page_with_title(questions_title(), [
        msg(),
        heading(__('Open questions'), 2),
        table([
            'Question' => __('Question'),
            'actions'  => ''
        ], $open_questions),
        heading(__('Answered questions'), 2),
        table([
            'Question'    => __('Question'),
            'answer_user' => __('Answered by'),
            'Answer'      => __('Answer'),
            'actions'     => ''
        ], $answered_questions),
        heading(__('Ask the Heaven'), 2),
        form([
            form_textarea('question', __('Your Question:'), ''),
            form_submit('submit', __('Send'))
        ], $ask_action)
    ], true);
}
