<?php
function user_questions() {
	global $user;

	if (!isset ($_REQUEST['action'])) {
		$open_questions = "";
		$questions = sql_select("SELECT * FROM `Questions` WHERE `AID`=0 AND `UID`=" . sql_escape($user['UID']));
		foreach ($questions as $question)
			$open_questions .= '<tr><td>' . str_replace("\n", '<br />', $question['Question']) . '</td><td><a href="' . page_link_to("user_questions") . '&action=delete&id=' . $question['QID'] . '">Delete</a></td><tr>';

		$answered_questions = "";
		$questions = sql_select("SELECT * FROM `Questions` WHERE `AID`>0 AND `UID`=" . sql_escape($user['UID']));
		foreach ($questions as $question) {
			$answered_questions .= '<tr><td>' . str_replace("\n", '<br />', $question['Question']) . '</td>';
			$answered_questions .= '<td>' . UID2Nick($question['AID']) . '</td><td>' . str_replace("\n", '<br />', $question['Answer']) . '</td>';
			$answered_questions .= '<td><a href="' . page_link_to("user_questions") . '&action=delete&id=' . $question['QID'] . '">Delete</a></td><tr>';
		}

		return template_render('../templates/user_questions.html', array (
			'link' => page_link_to("user_questions"),
			'open_questions' => $open_questions,
			'answered_questions' => $answered_questions
		));
	} else {
		switch ($_REQUEST['action']) {
			case 'ask' :
				$question = trim(preg_replace("/([^\p{L}\p{P}\p{Z}\p{N}\n]{1,})/ui", '', strip_tags($_REQUEST['question'])));
				if ($question != "") {
					sql_query("INSERT INTO `Questions` SET `UID`=" . sql_escape($user['UID']) . ", `Question`='" . sql_escape($question) . "'");
					header("Location: " . page_link_to("user_questions"));
				} else
					return error("Please enter a question!");
				break;
			case 'delete' :
				if (isset ($_REQUEST['id']) && preg_match("/^[0-9]{1,11}$/", $_REQUEST['id']))
					$id = $_REQUEST['id'];
				else
					return error("Incomplete call, missing Question ID.");

				$question = sql_select("SELECT * FROM `Questions` WHERE `QID`=" . sql_escape($id) . " LIMIT 1");
				if (count($question) > 0 && $question[0]['UID'] == $user['UID']) {
					sql_query("DELETE FROM `Questions` WHERE `QID`=" . sql_escape($id) . " LIMIT 1");
					header("Location: " . page_link_to("user_questions"));
				} else
					return error("No question found.");
				break;
		}
	}
}
?>