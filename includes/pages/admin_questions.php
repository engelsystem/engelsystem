<?php
function admin_questions_title() {
  return _("Answer questions");
}

function admin_new_questions() {
  global $user, $privileges;

  if (in_array("admin_questions", $privileges)) {
    $new_messages = sql_num_query("SELECT * FROM `Questions` WHERE `AID` IS NULL");

    if ($new_messages > 0)
      return info('<a href="' . page_link_to("admin_questions") . '">' . _('There are unanswered questions!') . '</a>', true);
  }

  return "";
}

function admin_questions() {
  global $user;

  if (! isset($_REQUEST['action'])) {
    $unanswered_questions_table = array();
    $questions = sql_select("SELECT * FROM `Questions` WHERE `AID` IS NULL");
    foreach ($questions as $question) {
      $user_source = User($question['UID']);
      if ($user_source === false)
        engelsystem_error("Unable to load user.");

      $unanswered_questions_table[] = array(
          'from' => User_Nick_render($user_source),
          'question' => str_replace("\n", "<br />", $question['Question']),
          'answer' => form(array(
              form_textarea('answer', '', ''),
              form_submit('submit', _("Save"))
          ), page_link_to('admin_questions') . '&action=answer&id=' . $question['QID']),
          'actions' => button(page_link_to("admin_questions") . '&action=delete&id=' . $question['QID'], _("delete"), 'btn-xs')
      );
    }

    $answered_questions_table = array();
    $questions = sql_select("SELECT * FROM `Questions` WHERE NOT `AID` IS NULL");
    foreach ($questions as $question) {
      $user_source = User($question['UID']);
      if ($user_source === false)
        engelsystem_error("Unable to load user.");

      $answer_user_source = User($question['AID']);
      if ($answer_user_source === false)
        engelsystem_error("Unable to load user.");
      $answered_questions_table[] = array(
          'from' => User_Nick_render($user_source),
          'question' => str_replace("\n", "<br />", $question['Question']),
          'answered_by' => User_Nick_render($answer_user_source),
          'answer' => str_replace("\n", "<br />", $question['Answer']),
          'actions' => button(page_link_to("admin_questions") . '&action=delete&id=' . $question['QID'], _("delete"), 'btn-xs')
      );
    }

    return page_with_title(admin_questions_title(), array(
        '<h2>' . _("Unanswered questions") . '</h2>',
        table(array(
            'from' => _("From"),
            'question' => _("Question"),
            'answer' => _("Answer"),
            'actions' => ''
        ), $unanswered_questions_table),
        '<h2>' . _("Answered questions") . '</h2>',
        table(array(
            'from' => _("From"),
            'question' => _("Question"),
            'answered_by' => _("Answered by"),
            'answer' => _("Answer"),
            'actions' => ''
        ), $answered_questions_table)
    ));
  } else {
    switch ($_REQUEST['action']) {
      case 'answer':
        if (isset($_REQUEST['id']) && preg_match("/^[0-9]{1,11}$/", $_REQUEST['id']))
          $id = $_REQUEST['id'];
        else
          return error("Incomplete call, missing Question ID.", true);

        $question = sql_select("SELECT * FROM `Questions` WHERE `QID`='" . sql_escape($id) . "' LIMIT 1");
        if (count($question) > 0 && $question[0]['AID'] == null) {
          $answer = trim(preg_replace("/([^\p{L}\p{P}\p{Z}\p{N}\n]{1,})/ui", '', strip_tags($_REQUEST['answer'])));

          if ($answer != "") {
            sql_query("UPDATE `Questions` SET `AID`='" . sql_escape($user['UID']) . "', `Answer`='" . sql_escape($answer) . "' WHERE `QID`='" . sql_escape($id) . "' LIMIT 1");
            engelsystem_log("Question " . $question[0]['Question'] . " answered: " . $answer);
            redirect(page_link_to("admin_questions"));
          } else
            return error("Gib eine Antwort ein!", true);
        } else
          return error("No question found.", true);
        break;
      case 'delete':
        if (isset($_REQUEST['id']) && preg_match("/^[0-9]{1,11}$/", $_REQUEST['id']))
          $id = $_REQUEST['id'];
        else
          return error("Incomplete call, missing Question ID.", true);

        $question = sql_select("SELECT * FROM `Questions` WHERE `QID`='" . sql_escape($id) . "' LIMIT 1");
        if (count($question) > 0) {
          sql_query("DELETE FROM `Questions` WHERE `QID`='" . sql_escape($id) . "' LIMIT 1");
          engelsystem_log("Question deleted: " . $question[0]['Question']);
          redirect(page_link_to("admin_questions"));
        } else
          return error("No question found.", true);
        break;
    }
  }
}
?>
