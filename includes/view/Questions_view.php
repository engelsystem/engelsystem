<?php

/**
 * @param array[] $open_questions
 * @param array[] $answered_questions
 * @param string  $ask_action
 * @return string
 */
function Questions_view($open_questions, $answered_questions, $ask_action)
{
    foreach ($open_questions as &$question) {
        $question['actions'] = '<a href="'
            . page_link_to('user_questions', ['action' => 'delete', 'id' => $question['QID']])
            . '">'
            . __('delete')
            . '</a>';
        $question['Question'] = str_replace("\n", '<br />', $question['Question']);
    }

    foreach ($answered_questions as &$question) {
        $question['Question'] = str_replace("\n", '<br />', $question['Question']);
        $question['Answer'] = str_replace("\n", '<br />', $question['Answer']);
        $question['actions'] = '<a href="'
            . page_link_to('user_questions', ['action' => 'delete', 'id' => $question['QID']])
            . '">'
            . __('delete')
            . '</a>';
    }

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
            form_submit('submit', __('Save'))
        ], $ask_action)
    ]);
}
