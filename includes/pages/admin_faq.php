<?php
function admin_faq() {
	if (!isset ($_REQUEST['action'])) {
		$faqs_html = "";
		$faqs = sql_select("SELECT * FROM `FAQ`");
		foreach ($faqs as $faq) {
			$faqs_html .= '<tr><td><dl><dt>' . $faq['Frage_de'] . '</dt><dd>' . $faq['Antwort_de'] . '</dd></dl></td><td><dl><dt>' . $faq['Frage_en'] . '</dt><dd>' . $faq['Antwort_en'] . '</dd></dl></td>';
			$faqs_html .= '<td><a href="' . page_link_to("admin_faq") . '&action=edit&id=' . $faq['FID'] . '">Edit</a></td></tr>';
		}
		return template_render('../templates/admin_faq.html', array (
			'link' => page_link_to("admin_faq"),
			'faqs' => $faqs_html
		));
	} else {
		switch ($_REQUEST['action']) {
			case 'create' :
				$frage = preg_replace("/([^\p{L}\p{P}\p{Z}\p{N}\n]{1,})/ui", '', strip_tags($_REQUEST['frage']));
				$antwort = preg_replace("/([^\p{L}\p{P}\p{Z}\p{N}\n]{1,})/ui", '', strip_tags($_REQUEST['antwort']));
				$question = preg_replace("/([^\p{L}\p{P}\p{Z}\p{N}\n]{1,})/ui", '', strip_tags($_REQUEST['question']));
				$answer = preg_replace("/([^\p{L}\p{P}\p{Z}\p{N}\n]{1,})/ui", '', strip_tags($_REQUEST['answer']));
				sql_query("INSERT INTO `FAQ` SET `Frage_de`='" . sql_escape($frage) . "', `Frage_en`='" . sql_escape($question) . "', `Antwort_de`='" . sql_escape($antwort) . "', `Antwort_en`='" . sql_escape($answer) . "'");
				header("Location: " . page_link_to("admin_faq"));
				break;

			case 'save' :
				if (isset ($_REQUEST['id']) && preg_match("/^[0-9]{1,11}$/", $_REQUEST['id']))
					$id = $_REQUEST['id'];
				else
					return error("Incomplete call, missing FAQ ID.");

				$faq = sql_select("SELECT * FROM `FAQ` WHERE `FID`=" . sql_escape($id) . " LIMIT 1");
				if (count($faq) > 0) {
					list ($faq) = $faq;

					$frage = preg_replace("/([^\p{L}\p{P}\p{Z}\p{N}\n]{1,})/ui", '', strip_tags($_REQUEST['frage']));
					$antwort = preg_replace("/([^\p{L}\p{P}\p{Z}\p{N}\n]{1,})/ui", '', strip_tags($_REQUEST['antwort']));
					$question = preg_replace("/([^\p{L}\p{P}\p{Z}\p{N}\n]{1,})/ui", '', strip_tags($_REQUEST['question']));
					$answer = preg_replace("/([^\p{L}\p{P}\p{Z}\p{N}\n]{1,})/ui", '', strip_tags($_REQUEST['answer']));
					sql_query("UPDATE `FAQ` SET `Frage_de`='" . sql_escape($frage) . "', `Frage_en`='" . sql_escape($question) . "', `Antwort_de`='" . sql_escape($antwort) . "', `Antwort_en`='" . sql_escape($answer) . "' WHERE `FID`=" . sql_escape($id) . " LIMIT 1");
					header("Location: " . page_link_to("admin_faq"));
				} else
					return error("No FAQ found.");
				break;

			case 'edit' :
				if (isset ($_REQUEST['id']) && preg_match("/^[0-9]{1,11}$/", $_REQUEST['id']))
					$id = $_REQUEST['id'];
				else
					return error("Incomplete call, missing FAQ ID.");

				$faq = sql_select("SELECT * FROM `FAQ` WHERE `FID`=" . sql_escape($id) . " LIMIT 1");
				if (count($faq) > 0) {
					list ($faq) = $faq;

					return template_render('../templates/admin_faq_edit_form.html', array (
						'link' => page_link_to("admin_faq"),
						'id' => $id,
						'frage' => $faq['Frage_de'],
						'antwort' => $faq['Antwort_de'],
						'question' => $faq['Frage_en'],
						'answer' => $faq['Antwort_en']
					));
				} else
					return error("No FAQ found.");
				break;

			case 'delete' :
				if (isset ($_REQUEST['id']) && preg_match("/^[0-9]{1,11}$/", $_REQUEST['id']))
					$id = $_REQUEST['id'];
				else
					return error("Incomplete call, missing FAQ ID.");

				$faq = sql_select("SELECT * FROM `FAQ` WHERE `FID`=" . sql_escape($id) . " LIMIT 1");
				if (count($faq) > 0) {
					list ($faq) = $faq;

					sql_query("DELETE FROM `FAQ` WHERE `FID`=" . sql_escape($id) . " LIMIT 1");
					header("Location: " . page_link_to("admin_faq"));
				} else
					return error("No FAQ found.");
				break;
		}
	}
}
?>