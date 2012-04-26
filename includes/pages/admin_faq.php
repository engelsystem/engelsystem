<?php
function admin_faq_create_edit_table($languages, $prefills = array()) {
	$form_questions = array('Question');
	$form_answers = array('Answer');
	foreach ($languages as $language) {
		$form_questions[] = '<textarea name="question[' . $language . ']" style="height: 4em;">'
			. (!empty($prefills[$language])? $prefills[$language]['question'] : '')
			. '</textarea>';
		$form_answers[] = '<textarea name="answer[' . $language . ']" style="height: 4em;">'
			. (!empty($prefills[$language])? $prefills[$language]['answer'] : '')
			. '</textarea>';
	}

	return table(
		array_merge(array(''), $languages),
		array($form_questions, $form_answers),
		false);
}

function admin_faq() {
	$languages = sql_select("SELECT DISTINCT `Sprache` FROM `FAQ`");
	$languages = array_map('array_shift', $languages);
	if (!isset ($_REQUEST['action'])) {
		$faqs = array();
		foreach ($languages as $language) {
			$lang_html .= '<th>' . $language . "</th>\n";
			$langfaqs = sql_select("SELECT `QID`, `Frage`, `Antwort` FROM `FAQ` WHERE `Sprache` = '" . sql_escape($language) . "'");
			foreach ($langfaqs as $langfaq) {
				if (!isset($faqs[$langfaq['QID']]))
					$faqs[$langfaq['QID']] = array();
				$faqs[$langfaq['QID']][$language] = sprintf('<dl><dt>%s</dt><dd>%s</dd></dl>', $langfaq['Frage'], $langfaq['Antwort']);
				$faqs[$langfaq['QID']]['edit'] = sprintf('<a href="%s&action=edit&id=%s">Edit</a>', page_link_to('admin_faq'), $langfaq['QID']);
			}
		}
		$faqs_html = table(array_merge(array_combine($languages, $languages), array('edit' => '')), $faqs);
		return template_render('../templates/admin_faq.html', array (
			'link' => page_link_to("admin_faq"),
			'faqs' => $faqs_html,
			'new_form' => admin_faq_create_edit_table($languages)
		));
	} else {
		switch ($_REQUEST['action']) {
			case 'create' :
			case 'save' :
				if ($_REQUEST['action'] == 'create') {
					sql_query("START TRANSACTION");
					$qid = sql_select("SELECT MAX(`QID`)+1 AS QID FROM `FAQ`");
					$qid = $qid[0]['QID'];
				}
				else {
					if (isset ($_REQUEST['id']) && preg_match("/^[0-9]{1,11}$/", $_REQUEST['id']))
						$qid = $_REQUEST['id'];
					else
						return error("Incomplete call, missing FAQ ID.", true);

					$faq = sql_select("SELECT `QID` FROM `FAQ` WHERE `QID`=" . sql_escape($qid));
					if (count($faq) == 0)
						return error("No FAQ found.", true);
				}
				$values = array();
				foreach ($_POST['question'] as $lang => $question) {
					if (!in_array($lang, $languages))
						continue;
					if (empty($question))
						sql_query("DELETE IGNORE FROM `FAQ` WHERE `QID` = $qid AND `Sprache` = '" . sql_escape($lang) . "'");
					else {
						$question = strip_item($question);
						$answer = strip_item($_POST['answer'][$lang]);
						$values[] = "('" . sql_escape($lang) . "', '" . sql_escape($question) . "', '" . sql_escape($answer) . "', $qid)";
					}
				}
				if (!empty($values))
					sql_query("REPLACE INTO `FAQ` (`Sprache`, `Frage`, `Antwort`, `QID`) VALUES " . implode(', ', $values));
				sql_query("COMMIT");

				header("Location: " . page_link_to("admin_faq"));
				break;

			case 'edit' :
				if (isset ($_REQUEST['id']) && preg_match("/^[0-9]{1,11}$/", $_REQUEST['id']))
					$id = $_REQUEST['id'];
				else
					return error("Incomplete call, missing FAQ ID.", true);

				$faq = sql_select("SELECT `Sprache`, `Frage`, `Antwort` FROM `FAQ` WHERE `QID`=" . sql_escape($id));
				if (count($faq) > 0) {
					$prefills = array();
					foreach ($faq as $row) {
						$prefills[$row['Sprache']] = array('question' => $row['Frage'], 'answer' => $row['Antwort']);
					}

					return template_render('../templates/admin_faq_edit_form.html', array (
						'link' => page_link_to("admin_faq"),
						'id' => $id,
						'form' => admin_faq_create_edit_table($languages, $prefills)
					));
				} else
					return error("No FAQ found.", true);
				break;

			case 'delete' :
				if (isset ($_REQUEST['id']) && preg_match("/^[0-9]{1,11}$/", $_REQUEST['id']))
					$id = $_REQUEST['id'];
				else
					return error("Incomplete call, missing FAQ ID.", true);

				$deleted = sql_query("DELETE FROM `FAQ` WHERE `QID`=" . sql_escape($id));
				if ($deleted) {
					header("Location: " . page_link_to("admin_faq"));
				} else
					return error("No FAQ found.", true);
				break;
		}
	}
}
?>
