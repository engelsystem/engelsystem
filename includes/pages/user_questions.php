<?php

use Engelsystem\Models\Question;

/**
 * @return string
 */
function questions_title()
{
    return __('Ask the Heaven');
}

/**
 * @return string
 */
function user_questions()
{
    $user = auth()->user();
    $request = request();

    if (!$request->has('action')) {
        $open_questions = $user->questionsAsked()->whereNull('answerer_id')->get();
        $answered_questions = $user->questionsAsked()->whereNotNull('answerer_id')->get();

        return Questions_view(
            $open_questions->all(),
            $answered_questions->all(),
            page_link_to('user_questions', ['action' => 'ask'])
        );
    } else {
        switch ($request->input('action')) {
            case 'ask':
                $question = request()->get('question');
                if (!empty($question) && $request->hasPostData('submit')) {
                    Question::create([
                        'user_id' => $user->id,
                        'text' => $question,
                    ]);

                    success(__('You question was saved.'));
                    redirect(page_link_to('user_questions'));
                } else {
                    return page_with_title(questions_title(), [
                        error(__('Please enter a question!'), true)
                    ]);
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
                    return error(__('Incomplete call, missing Question ID.'), true);
                }

                $question = Question::find($question_id);
                if (!empty($question) && $question->user_id == $user->id) {
                    $question->delete();
                    redirect(page_link_to('user_questions'));
                } else {
                    return page_with_title(questions_title(), [
                        error(__('No question found.'), true)
                    ]);
                }
                break;
        }
    }

    return '';
}
