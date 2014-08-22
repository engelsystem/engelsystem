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
  
  return page_with_title(questions_title(), array(
      msg(),
      '<h2>' . _("Open questions") . '</h2>',
      table(array(
          'Question' => _("Question"),
          'actions' => "" 
      ), $open_questions),
      '<h2>' . _("Answered questions") . '</h2>',
      table(array(
          'Question' => _("Question"),
          'answer_user' => _("Answered by"),
          'Answer' => _("Answer"),
          'actions' => "" 
      ), $answered_questions),
      '<h2>' . _("Ask an archangel") . '</h2>',
      form(array(
          form_textarea('question', _("Your Question:"), ""),
          form_submit('submit', _("Save")) 
      ), $ask_action) 
  ));
}

?>