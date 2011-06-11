<?php
function admin_new_questions() {
	global $user, $privileges;

	if (in_array("admin_questions", $privileges)) {
		$new_messages = sql_num_query("SELECT * FROM `Questions` WHERE `AID`=0");

		if ($new_messages > 0)
			return '<p class="notice"><a href="' . page_link_to("admin_questions") . '">There are unanswered questions!</a></p><hr />';
	}

	return "";
}

function admin_questions() {
	global $user;

	if (!isset ($_REQUEST['action'])) {
		$open_questions = "";
		$questions = sql_select("SELECT * FROM `Questions` WHERE `AID`=0");
		foreach ($questions as $question)
			$open_questions .= template_render(
				'../templates/admin_question_unanswered.html', array (
				'question_nick' => UID2Nick($question['UID']),
				'question_id'   => $question['QID'],
				'link'          => page_link_to("admin_questions"),
				'question'      => str_replace("\n", '<br />', $question['Question'])
			));

		$answered_questions = "";
		$questions = sql_select("SELECT * FROM `Questions` WHERE `AID`>0");

		foreach ($questions as $question)
			$answered_questions .= template_render(
				'../templates/admin_question_answered.html', array (
				'question_id'   => $question['QID'],
				'question_nick' => UID2Nick($question['UID']),
				'question'      => str_replace("\n", "<br />", $question['Question']),
				'answer_nick'   => UID2Nick($question['AID']),
				'answer'        => str_replace("\n", "<br />", $question['Answer']),
				'link'          => page_link_to("admin_questions"),
			));

		return template_render('../templates/admin_questions.html', array (
			'link' => page_link_to("admin_questions"),
			'open_questions' => $open_questions,
			'answered_questions' => $answered_questions
		));
	} else {
		switch ($_REQUEST['action']) {
			case 'answer' :
				if (isset ($_REQUEST['id']) && preg_match("/^[0-9]{1,11}$/", $_REQUEST['id']))
					$id = $_REQUEST['id'];
				else
					return error("Incomplete call, missing Question ID.");

				$question = sql_select("SELECT * FROM `Questions` WHERE `QID`=" . sql_escape($id) . " LIMIT 1");
				if (count($question) > 0 && $question[0]['AID'] == "0") {
					$answer = trim(preg_replace("/([^\p{L}\p{P}\p{Z}\p{N}\n]{1,})/ui", '', strip_tags($_REQUEST['answer'])));

					if ($answer != "") {
						sql_query("UPDATE `Questions` SET `AID`=" . sql_escape($user['UID']) . ", `Answer`='" . sql_escape($answer) . "' WHERE `QID`=" . sql_escape($id) . " LIMIT 1");
						header("Location: " . page_link_to("admin_questions"));
					} else
						return error("Please enter an answer!");
				} else
					return error("No question found.");
				break;
			case 'delete' :
				if (isset ($_REQUEST['id']) && preg_match("/^[0-9]{1,11}$/", $_REQUEST['id']))
					$id = $_REQUEST['id'];
				else
					return error("Incomplete call, missing Question ID.");

				$question = sql_select("SELECT * FROM `Questions` WHERE `QID`=" . sql_escape($id) . " LIMIT 1");
				if (count($question) > 0) {
					sql_query("DELETE FROM `Questions` WHERE `QID`=" . sql_escape($id) . " LIMIT 1");
					header("Location: " . page_link_to("admin_questions"));
				} else
					return error("No question found.");
				break;
		}
	}
}
?>
