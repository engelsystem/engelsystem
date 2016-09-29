<?php

function Questions_view($open_questions, $answered_questions, $ask_action) {
  foreach ($open_questions as &$question) {
    $question['actions'] = '<a href="' . page_link_to("user_questions") . '&action=delete&id=' . $question['QID'] . '">' . _("delete") . '</a>';
    $question['Question'] = str_replace("\n", '<br />', $question['Question']);
  }
  
  foreach ($answered_questions as &$question) {
    $question['Question'] = str_replace("\n", '<br />', $question['Question']);
    $question['Answer'] = str_replace("\n", '<br />', $question['Answer']);
    $question['actions'] = '<a href="' . page_link_to("user_questions") . '&action=delete&id=' . $question['QID'] . '">' . _("delete") . '</a>';
  }
  
  return page_with_title(questions_title(), [
      msg(),
      heading(_("Open questions"), 2),
      table([
          'Question' => _("Question"),
          'actions' => "" 
      ], $open_questions),
      heading(_("Answered questions"), 2),
      table([
          'Question' => _("Question"),
          'answer_user' => _("Answered by"),
          'Answer' => _("Answer"),
          'actions' => "" 
      ], $answered_questions),
      heading(_("Ask an archangel"), 2),
      form([
          form_textarea('question', _("Your Question:"), ""),
          form_submit('submit', _("Save")) 
      ], $ask_action) 
  ]);
}

?>